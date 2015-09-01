<?php

function xssafe($data,$encoding='UTF-8')
{
	return htmlspecialchars($data,ENT_QUOTES,$encoding);
}
function xecho($data)
{
	return xssafe($data);
}

class Plan {
	public $pid;
	public $currentCredits;
	public $maxCredits;
	public $salt;
	public $private;
	
	private $nonce;
	private $fullSchedule = array();

	function __construct($n, $pid = null)
	{
		global $db;

		$this->nonce = $n;
		$this->pid = $pid;
		$this->currentCredits = 0;

		if($pid != null)
		{
			$fetchStmt = $db->stmt_init();
			$fetchStmt->prepare("SELECT `maxCredits`,`rand`,`private` FROM `plans` WHERE `id` = ?");
			$fetchStmt->bind_param('s', $pid);
			$fetchStmt->execute();
			$fetchStmt->bind_result($theMax, $theSalt, $isPriv);

			// Fetch the result of the query
		    while($fetchStmt->fetch())
		    {
		    	$this->maxCredits = $theMax;
		        $this->salt = $theSalt;
		        $this->private = $isPriv;
		    }

			$fetchStmt->close();
		}
	}

	function setCurrentCredits($pid, $cred)
	{
		global $db;

		$updateStmt = $db->stmt_init(); 
		$updateStmt->prepare("UPDATE `plans` SET currentCredits=? WHERE id=?"); 
	    $updateStmt->bind_param('is', $cred, $pid);

	    $updateStmt->execute();

	    $updateStmt->close();
	}

	function setPassword($pass)
	{
		global $db;

		$pwdHash = $this->pbkdf2($pass, $this->salt, 1000, 32);

		$updateStmt = $db->stmt_init(); 
		$updateStmt->prepare("UPDATE `plans` SET password=? WHERE id=?");
	    $updateStmt->bind_param('ss', $pwdHash, $this->pid);

	    $updateStmt->execute();

	    $updateStmt->close();

	    $this->private = "1";
	    $this->setPrivate(1);
	}

	function setPrivate($isPrivate)
	{
		global $db;

		$updateStmt = $db->stmt_init(); 
		$updateStmt->prepare("UPDATE `plans` SET private=? WHERE id=?"); 
	    $updateStmt->bind_param('is', $isPrivate, $this->pid);

	    $updateStmt->execute();

	    $updateStmt->close();
	}

	function getSemester($sem)
	{
		global $fullSchedule;

		//Don't call if $fullSchedule is empty!
		foreach($fullSchedule as $i => $class)
		{
			if($class['semester'] == $sem)
			{
				echo "<div id='". $class['id'] ."'>
						<div class='remove' id='". $class['id'] ."r' rel='tooltip' data-trigger='hover' data-placement='top' data-animation='true' data-original-title='Delete class' onclick='removeClass(". $class['id'] .",\"". $this->nonce ."\")'></div>";

				if($class['completed'] == 1)
				{
					echo "<input class='class_complete' id='". $class['id'] ."cb' rel='tooltip' data-trigger='hover' data-placement='left' data-animation='true' data-original-title='Toggle completion' onclick='comp(". $class['id'] .",\"". $this->nonce ."\")' type='checkbox' checked>
					    <input class='class_input comp' id='". $class['id'] ."n' rel='tooltip' data-trigger='focus' data-placement='top' data-animation='true' data-original-title='Enter class name' type='text' value='". xecho($class['name']) ."' disabled='true'>
					    <input class='class_credits comp' id='". $class['id'] ."c' rel='tooltip' data-trigger='focus' data-placement='top' data-animation='true' data-original-title='Enter class credits' data-credits='". $class['credits'] ."' type='text' value='". $class['credits'] ."' maxlength='3' disabled='true'>";
				}
				else
				{
					echo "<input class='class_complete' id='". $class['id'] ."cb' rel='tooltip' data-trigger='hover' data-trigger='hover' data-placement='left' data-animation='true' data-original-title='Toggle completion' onclick='comp(". $class['id'] .",\"". $this->nonce ."\")' type='checkbox' >
					    <input class='class_input' id='". $class['id'] ."n' rel='tooltip' data-trigger='focus' data-placement='top' data-animation='true' data-original-title='Enter class name' type='text' value='". xecho($class['name']) ."' disabled='true'>
					    <input class='class_credits' id='". $class['id'] ."c' rel='tooltip' data-trigger='focus' data-placement='top' data-animation='true' data-original-title='Enter class credits' data-credits='". $class['credits'] ."' type='text' value='". $class['credits'] ."' maxlength='3' disabled='true'>";
				}
				        
				echo "<div class='save' id='". $class['id'] ."s' rel='tooltip' data-trigger='hover' data-placement='top' data-animation='true' data-original-title='Save changes' onclick='save(". $sem .",". $class['id'] .",\"". $this->pid ."\",\"". $this->nonce ."\")'></div>
				   	    <div class='edit' id='". $class['id'] ."e' rel='tooltip' data-trigger='hover' data-placement='top' data-animation='true' data-original-title='Edit class' onclick='edit(". $class['id'] .")'></div>
					 </div>			 
					 ";
			}
		}
	}

	function createActionButtons($sem)
	{
		global $nonce;
		global $pid;

		echo "<hr /><button class='btn btn-success actions' onclick='addClass(". $sem .",\"". $this->pid ."\",\"". $this->nonce ."\")'>Add Class</button>&nbsp;&nbsp;<button class='btn btn-success actions' onclick='completeAll(". $sem .",\"". $this->nonce ."\")'>Complete All</button>";
	}

	function exists($pid)
	{
		global $db;
		$exists = TRUE;

		// Create statement object
		$stmt = $db->stmt_init();
		 
		// Create a prepared statement
		if($stmt->prepare("SELECT `id` FROM `plans` WHERE `id` = ?"))
		{
		    // Bind your variable to replace the ?
		    $stmt->bind_param('s', $pid);

		    // Execute query
		    $stmt->execute();

		    $stmt->store_result();

		    if($stmt->num_rows == 0)
		    	$exists = FALSE;

		    // Close statement object
		   $stmt->close();
		}
		return $exists;
	}

	//Populates $fullSchedule with template for major
	//Creates new entry in database for this plan
	function createPlanFromTemplate($major)
	{
		global $db;
		global $fullSchedule;

		$id = $this->str_rand(5);

		//Create the plan in the Plans table
		// Create statement object
		$insertStmt = $db->stmt_init();
		 
		// Create a prepared statement
		if($insertStmt->prepare("INSERT INTO `plans` (`id`, `currentCredits`, `maxCredits`, `rand`, `private`) VALUES (?, ?, ?, ?, ?)"))
		{
		    // Bind your variable to replace the ?
		    $insertStmt->bind_param('siisi', $id, $curCreds, $maxCreds, $rand, $priv);

		    $curCreds = 0;

		    if(strcasecmp($major,"dm") == 0 || strcasecmp($major,"sue") == 0)
		    	$maxCreds = 120;
		    else
		   		$maxCreds = 128;

		    $rand = $this->str_rand(8);
		    $priv = 0;

		    // Execute query
		    $insertStmt->execute();

		    // Close statement object
		   $insertStmt->close();
		}

		//Now populate the classes table with the template
		// Create statement object
		$fetchStmt = $db->stmt_init();
		 
		// Create a prepared statement
		if($fetchStmt->prepare("SELECT `semester`,`name`,`credits` FROM `templates` WHERE `major` = ?"))
		{
		    // Bind your variable to replace the ?
		    $fetchStmt->bind_param('s', $major);

		    // Execute query
		    $fetchStmt->execute();

		    $fetchStmt->store_result();

		    $fetchStmt->bind_result($res_semester,$res_name, $res_credits);

		    $insertStmt2 = $db->stmt_init();
			// Fetch the result of the query & insert
			while($fetchStmt->fetch())
			{
				if($insertStmt2->prepare("INSERT INTO `classes` (`id`, `pid`, `semester`, `name`, `credits`, `completed`) VALUES (?, ?, ?, ?, ?, ?)"))
				{
				    // Bind your variable to replace the ?
				    $insertStmt2->bind_param('isisdi', $cid, $id, $res_semester, $res_name, $res_credits, $comp);

				    $comp = 0;
				    $cid = $this->str_rand(9, "numeric");

				    // Execute query
				    $insertStmt2->execute();
				}
			}
			// Close statement object
			$insertStmt2->close();

		    // Close statement object
		    $fetchStmt->close();
		}

		return $id;
	}
	
	//Populated $fullSchedule with courses for plan ID
	function loadClasses($pid)
	{
		global $db;
		global $fullSchedule;
		global $currentCredits;

		$id = $pid;

		// Create statement object
		$stmt = $db->stmt_init();
		 
		// Create a prepared statement
		if($stmt->prepare("SELECT `id`,`semester`,`name`,`credits`,`completed` FROM `classes` WHERE `pid` = ?"))
		{
		    // Bind your variable to replace the ?
		    $stmt->bind_param('s', $pid);

		    // Execute query
		    $stmt->execute();

		    $stmt->bind_result($res_id, $res_semester,$res_name, $res_credits, $res_completed);

			// Fetch the result of the query
			while($stmt->fetch())
			{
				$fullSchedule[] = array(
							"id" => $res_id,
		   					"semester" => $res_semester,
		   					"name" => $res_name,
		   					"credits" => $res_credits,
		   					"completed" => $res_completed,
		   		);

				//count completed credits
				if($res_completed == 1)
					$this->currentCredits += $res_credits;
				
			}

		    // Close statement object
		    $stmt->close();
		}

		//Save current num credits to db
		$this->setCurrentCredits($pid, $this->currentCredits);

		//Log last accessed time
		$updateStmt = $db->stmt_init(); 
		$updateStmt->prepare("UPDATE `plans` SET lastaccessed=? WHERE id=?"); 
	    $updateStmt->bind_param('is', $time, $pid);

	    $time = time();

	    $updateStmt->execute();

	    $updateStmt->close();

		//print_r($fullSchedule);
		return json_encode($fullSchedule);
	}

	function replaceClass($sem, $cid, $pid, $name, $creds, $comp)
	{
		global $db;

		// Create statement object
		$stmt = $db->stmt_init();
		 
		//Delete the class
		if($stmt->prepare("DELETE FROM `classes` WHERE `id` = ?"))
		{
		    // Bind your variable to replace the ?
		    $stmt->bind_param('i', $cid);

		    // Execute query
		    $stmt->execute();

		    // Close statement object
		   $stmt->close();
		}

		//Add the class
		$insertStmt = $db->stmt_init(); 
		$insertStmt->prepare("INSERT INTO `classes` (`id`,`pid`, `semester`, `name`, `credits`, `completed`) VALUES (?, ?, ?, ?, ?, ?)");
	    $insertStmt->bind_param('isisdi', $cid, $pid, $sem, $name, $creds, $comp);

	    $insertStmt->execute();

	    $insertStmt->close();
	}

	function alterClassCompletion($cid, $comp)
	{
		global $db;

		$updateStmt = $db->stmt_init(); 
		$updateStmt->prepare("UPDATE `classes` SET completed=? WHERE id=?"); 
	    $updateStmt->bind_param('ii', $comp, $cid);

	    $updateStmt->execute();

	    $updateStmt->close();
	}

	function removeClass($cid)
	{
		global $db;

		$removeStmt = $db->stmt_init(); 
		$removeStmt->prepare("DELETE FROM `classes` WHERE `id` = ?");
	    $removeStmt->bind_param('i', $cid);

	    $removeStmt->execute();

	    $removeStmt->close();
	}

	function checkPassword($pass)
	{
		global $db;

		$valid = false;

		$inputHash = $this->pbkdf2($pass, $this->salt, 1000, 32);

		$stmt = $db->stmt_init();
		 
		if($stmt->prepare("SELECT `password` FROM `plans` WHERE `id` = ?"))
		{
		    $stmt->bind_param('s', $this->pid);
		    $stmt->execute();

		    $stmt->bind_result($hash);

			while($stmt->fetch())
			{
				$valid = $inputHash == $hash;
			}

			$stmt->close();
		}

		return $valid;
	}

	function checkClass($classId)
	{
		global $db;

		$valid = false;

		$stmt = $db->stmt_init();
		 
		if($stmt->prepare("SELECT `pid` FROM `classes` WHERE `id` = ?"))
		{
		    $stmt->bind_param('s', $classId);
		    $stmt->execute();

		    $stmt->bind_result($pid);

			while($stmt->fetch())
			{
				$valid = $this->pid == $pid;
			}

			$stmt->close();
		}

		return $valid;
	}

	function removePassword()
	{
		global $db;

		$updateStmt = $db->stmt_init(); 
		$updateStmt->prepare("UPDATE `plans` SET private=0 WHERE id=?"); 
	    $updateStmt->bind_param('s', $this->pid);

	    $updateStmt->execute();

	    $updateStmt->close();
	}

	/** PBKDF2 Implementation (described in RFC 2898)
	*
	*  @param string p password
	*  @param string s salt
	*  @param int c iteration count (use 1000 or higher)
	*  @param int kl derived key length
	*  @param string a hash algorithm
	*
	*  @return string derived key
	*/
	function pbkdf2( $p, $s, $c, $kl, $a = 'sha256' )
	{

		$hl = strlen(hash($a, null, true)); # Hash length
		$kb = ceil($kl / $hl);              # Key blocks to compute
		$dk = '';                           # Derived key

		# Create key
		for ( $block = 1; $block <= $kb; $block ++ ) {

			# Initial hash for this block
			$ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);

			# Perform block iterations
			for ( $i = 1; $i < $c; $i ++ )

				# XOR each iterate
				$ib ^= ($b = hash_hmac($a, $b, $p, true));

			$dk .= $ib; # Append iterated block
		}

		# Return derived key of correct length
		return substr($dk, 0, $kl);
	}

	# Snippet from PHP Share: http://www.phpshare.org

	/**
	* Generate and return a random string
	*
	* The default string returned is 8 alphanumeric characters.
	*
	* The type of string returned can be changed with the "seeds" parameter.
	* Four types are - by default - available: alpha, numeric, alphanum and hexidec. 
	*
	* If the "seeds" parameter does not match one of the above, then the string
	* supplied is used.
	*
	* @author      Aidan Lister <aidan@php.net>
	* @version     2.1.0
	* @link        http://aidanlister.com/repos/v/function.str_rand.php
	* @param       int     $length  Length of string to be generated
	* @param       string  $seeds   Seeds string should be generated from
	*/
	function str_rand($length = 8, $seeds = 'alphanum')
	{
		// Possible seeds
		$seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
		$seedings['numeric'] = '0123456789';
		$seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
		$seedings['hexidec'] = '0123456789abcdef';

		// Choose seed
		if (isset($seedings[$seeds]))
		{
			$seeds = $seedings[$seeds];
		}

		// Seed generator
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		mt_srand($seed);

		// Generate
		$str = '';
		$seeds_count = strlen($seeds);

		for ($i = 0; $length > $i; $i++)
		{
			$str .= $seeds{mt_rand(0, $seeds_count - 1)};
		}

		return $str;
	}
}
?>
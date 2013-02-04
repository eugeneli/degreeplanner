<?php
session_start();
include("fingerprint.php");
include("inc/classes/plan.class.php");
include_once('inc/db.php');

$plan = apc_fetch('plan');

if($_SESSION['n'] == $_POST['non'])
{
	if($_GET['a'] == "c")
	{
		$plan->alterClassCompletion($_POST['classid'], $_POST['comp']);
	}
	else if($_GET['a'] == "s")
	{
		if(strlen($_POST['creds']) < 4)
			$plan->replaceClass($_POST['semester'], $_POST['classid'], $_POST['planid'], $_POST['name'], $_POST['creds'], $_POST['comp']);
	}
	else if($_GET['a'] == "r")
	{
		$plan->removeClass($_POST['classid']);
	}
	else if($_GET['a'] == "l")
	{
		if($plan->checkPassword($_POST['pwd']))
		{
			$_SESSION['fingerprint'] = generateFingerprint();
			$_SESSION[$plan->pid] = true;
		}
		else
			echo "0";
	}
	else if($_GET['a'] == "sp")
	{
		$plan->setPassword($_POST['pwd']);
	}
	else if($_GET['a'] == "rp")
	{
		$plan->removePassword();
	}
}
?>
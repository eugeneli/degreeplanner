<?php
session_start();
include("inc/classes/plan.class.php");
include("fingerprint.php");
include_once('inc/db.php');

//Create nonce to prevent CSRF
if(!isset($_SESSION['n']))
  $_SESSION['n'] = md5(rand().time());

if(isset($_GET['major']) && !isset($_GET['p']) && strcspn($_GET['major'], '0123456789)[]{}+_()*&^%$#@!?|=~,./\\:;"<>') == strlen($_GET['major']))
{
  //Create plan object
  $plan = new Plan($_SESSION['n']);

  //Store it
  apc_store('plan', $plan);

  $planID = $plan->createPlanFromTemplate($_GET['major']);
  echo "<script> window.location = 'plan.php?p=". $planID ."'</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>NYU-Poly Degree Planner</title>
    <link href="assets/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <link href="assets/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.2.2/bootstrap.min.js"></script>
    <script src="assets/planner.js"></script>


    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
    <?php
      //Check GET variables
        if(!isset($_GET['major']) && isset($_GET['p']))
        {
          //Create plan object
          $plan = new Plan($_SESSION['n'], $_GET['p']);

          //Store it
          apc_store('plan', $plan); 

          if($plan->exists($_GET['p']))
          {
            if(($plan->private == "1" && !isset($_SESSION[$plan->pid])) || ($plan->private == "1" && !isValidFingerprint(generateFingerprint())))
            {
              echo "<div class='modal show'>
                      <div class='modal-header'>
                        <h3><img src='assets/img/lock.gif' />  Private plan</h3>
                      </div>
                      <div class='modal-body'>
                        <div id='pwdError' class='alert alert-error'></div>
                        <p>Please enter the password for this plan to continue:</p>
                        <input type='password' id='planPwd'>
                      </div>
                      <div class='modal-footer'>
                        <a href='#' class='btn btn-success' onclick='login(\"". $_SESSION['n'] ."\")'>Submit</a>
                      </div>
                    </div>'";
            }
            else if((!isset($_SESSION[$plan->pid]) && $plan->private == "0") || $_SESSION[$plan->pid] == true)
              $jsonSchedule = $plan->loadClasses($_GET['p']);
          }
          else
          {
            echo "<script> window.location = '/planner'</script>";
            exit;
          }
        }
    ?>
    <script type="text/javascript">
      <?php
        if(isset($jsonSchedule))
          echo "var fullSchedule = ". $jsonSchedule .";";
        else
          echo "var fullSchedule = [];";

        echo "var currentCredits = ". $plan->currentCredits .";";
        echo "var maxCredits = ". $plan->maxCredits .";";
      ?>
    </script>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <span class="brand">NYU-Poly Degree Planner</span>
          <ul class="nav">
              <li>
                <div class="barEntry counter">
                  <span id="currentCredits"> <?php echo $plan->currentCredits; ?> </span>/
                  <span id="maxCredits"> <?php echo $plan->maxCredits; ?> </span>
                </div>
              </li>
              <li>
                <div class="btn-group">
                  <a class="btn btn-success dropdown-toggle" data-toggle="dropdown" href="#">
                    Options
                    <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    <li><a href="#changePassword" data-toggle="modal">Set/Change Password</a></li>
                    <li><a href="#" onclick=<?php echo "removePassword('". $_SESSION['n'] ."')" ?>>Remove Password</a></li>
                  </ul>
                </div>
              </li>
            </ul>
          <div class="progressBarContainer">
            <div class="progress progress-striped">
              <div class="bar" id="degreeProgress" style=<?php echo "\"width: ". ($plan->currentCredits/$plan->maxCredits)*100 ."%\""; ?>></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
      if($plan->private != "1" || ($plan->private == 1 && $_SESSION[$plan->pid] == true))
      {
        ?>
     <div id="changePassword" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="changePassword" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>Enter new password below</h3>
      </div>
      <div class="modal-body">
        <div id="pwdStatus" class="alert" style="display:none;"></div>
        <input type="password" id="setplanPwd">
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
        <button class="btn btn-success" onclick=<?php echo "setPassword(\"". $_SESSION['n'] ."\")" ?>>Save changes</button>
      </div>
    </div>
   <!-- Year 1 -->
    <div class="container">
      <div id="toast" class="alert" style="display:none;"></div>
      <div id="welcome" class="alert" style="display:none;"></div>
      <div class="row-fluid">
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="1">
              <h4>Fall Semester</h4>
              <?php $plan->getSemester(1); ?>
            </div>
            <?php $plan->createActionButtons(1); ?>
          </div>
        </div>
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="2">
              <h4>Spring Semester</h4>
              <?php $plan->getSemester(2); ?>
            </div>
            <?php $plan->createActionButtons(2); ?>
          </div>
        </div>
      </div>

      <!-- Year 2 -->
      <div class="row-fluid">
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="3">
              <h4>Fall Semester</h4>
              <?php $plan->getSemester(3); ?>
            </div>
            <?php $plan->createActionButtons(3); ?>
          </div>
        </div>
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="4">
              <h4>Spring Semester</h4>
              <?php $plan->getSemester(4); ?>
            </div>
            <?php $plan->createActionButtons(4); ?>
          </div>
        </div>
      </div>

      <!-- Year 3 -->
      <div class="row-fluid">
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="5">
              <h4>Fall Semester</h4>
              <?php $plan->getSemester(5); ?>
            </div>
            <?php $plan->createActionButtons(5); ?>
          </div>
        </div>
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="6">
              <h4>Spring Semester</h4>
              <?php $plan->getSemester(6); ?>
            </div>
            <?php $plan->createActionButtons(6); ?>
          </div>
        </div>
      </div>

      <!-- Year 4 -->
      <div class="row-fluid">
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="7">
              <h4>Fall Semester</h4>
              <?php $plan->getSemester(7); ?>
            </div>
            <?php $plan->createActionButtons(7); ?>
          </div>
        </div>
        <div class="span5 semester">
          <div class="offset1 span10 semester_box">
            <div id="8">
              <h4>Spring Semester</h4>
              <?php $plan->getSemester(8); ?>
            </div>
            <?php $plan->createActionButtons(8); ?>
          </div>
        </div>
      </div>
      <script type="text/javascript">
      <?php echo "
                if(document.cookie.indexOf('". $plan->pid ."') == -1)
                { 
                  $(\"#welcome\").html(\"Welcome! Don't forget to bookmark this page or save your plan's unique ID (". $plan->pid .") !\");
                  $(\"#welcome\").css(\"display\", \"block\");

                  var now = new Date();
                  var time = now.getTime();
                  time += 36000 * 999999999;
                  now.setTime(time);
                  document.cookie = 
                    '". $plan->pid ."=' + 1 + 
                    '; expires=' + now.toGMTString() + 
                    '; path=/';
                }
                ";
      ?>
      </script>
<?php 
}
?>
    </div> <!-- /container -->
    <script type="text/javascript">
      $(document).ready(function() {
        refreshListeners();
       });
      jQuery('.class_credits').keyup(function () { 
          this.value = this.value.replace(/[^0-9\.]/g,'');
      });
    </script>
    
    <!-- Start of StatCounter Code for Default Guide -->
<script type="text/javascript">
var sc_project=8596034; 
var sc_invisible=1; 
var sc_security="123324e4"; 
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div class="statcounter"><a title="web statistics"
href="http://statcounter.com/free-web-stats/"
target="_blank"><img class="statcounter"
src="http://c.statcounter.com/8596034/0/123324e4/1/"
alt="web statistics"></a></div></noscript>
<!-- End of StatCounter Code for Default Guide -->
  </body>

</html>
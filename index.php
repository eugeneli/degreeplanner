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
    <script type="text/javascript">
    function redir(m)
    {
      window.location = "plan.php?major="+m;
    }
    </script>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <span class="brand">NYU-Poly Degree Planner</span>
            <div class="nav-collapse collapse">
            <ul class="nav">
              <div class="btn-group">
                  <a href="#about" class="btn btn-success" data-toggle="modal" href="#">
                    About
                  </a>
                </div>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div id="about" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="about" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>About</h3>
      </div>
      <div class="modal-body">
        Hi there, and thanks for actually taking a look at the "about" message!
        <br /><br />
        I made this because I wanted an easy, graphical way of keeping track of how I'm progressing towards my degree. Excel is powerful, though not very user friendly. Plus, I would need to sync that excel sheet across
        all my devices; it was too much of a hassle. Then I thought, why not keep track of my degree progress on ... *drumroll* the CLOUD. (Sorry, mandatory web 5.0 buzzword).
        <br />
        <br />
        So, I made this. All degree plans are stored on a central server, accessible from anywhere you have an internet connection. I spent several days working on this, so I hope you enjoy it and find it helpful.
        <br />
        If you want any feature requests or whatever, just email me: hello [at] itseugene.li
        <br />
        <br />
        Also, check out my NYU-Poly app <a href="https://play.google.com/store/apps/details?id=com.lambdai.poly">here</a>.
        <br />
        And my Team Fortress 2 app <a href="https://play.google.com/store/apps/details?id=com.lambdai.camfortress">here</a>.
        <br />
        <br />
        Thank you, and have a safe, pleasant, and positive day.
        <br />
        <br />
        Eugene Li - Computer Science '15
        <br />
        <a href="http://itseugene.li">http://itseugene.li</a>
      </div>
<!--       <div class="modal-footer">
        <button class="btn btn-success" data-dismiss="modal" aria-hidden="true">Close</button>
      </div> -->
    </div>
    <div class="container-fluid">
      <div class="giantPessimisticMessageContainer">
        <div class="page-header">
          <h1 class="giantPessimisticMessage">Plan your future!</h1>
          <h2 class="giantPessimisticMessage">
            <i>Try</i> <span style="font-size:10pt">(really hard)</span> to graduate in 4 years!
            <br />
            <small>To begin, choose your major or start from scratch!</small>
          </h2>
          
        </div>
      </div>
    </div>
    <div class="row-fluid">
      <ul class="thumbnails" style="width:1200px;margin:auto;">
        <li>
          <a href="plan.php?major=none" class="thumbnail">
            <img src="assets/img/majors/scratch.jpg">
          </a>
        </li>
        <li>
          <a href="plan.php?major=BMS" class="thumbnail">
            <img src="assets/img/majors/bms.jpg">
          </a>
        </li>
        <li>
          <a href="plan.php?major=BTM" class="thumbnail">
            <img src="assets/img/majors/btm.jpg">
          </a>
        </li>
       <li>
          <a href="plan.php?major=CBE" class="thumbnail">
            <img src="assets/img/majors/cbe.jpg">
          </a>
        </li>
      </ul>
    </div>
    <div class="row-fluid">
      <ul class="thumbnails" style="width:1200px;margin:auto;">
        <li>
         <a href="plan.php?major=CE" class="thumbnail">
            <img src="assets/img/majors/ce.jpg">
          </a>
        </li>
        <li>
          <a href="plan.php?major=CompE" class="thumbnail">
            <img src="assets/img/majors/compe.jpg">
          </a>
        </li>
        <li>
          <a href="plan.php?major=CS" class="thumbnail">
            <img src="assets/img/majors/cs.jpg">
          </a>
        </li>
       <li>
          <a href="plan.php?major=EE" class="thumbnail">
            <img src="assets/img/majors/ee.jpg">
          </a>
        </li>
      </ul>
    </div>
    <div class="row-fluid">
      <ul class="thumbnails" style="width:1200px;margin:auto;">
        <li>
          <a href="plan.php?major=DM" class="thumbnail">
            <img src="assets/img/majors/dm.jpg">
          </a>
        </li>
        <li>
          <a href="plan.php?major=Math" class="thumbnail">
            <img src="assets/img/majors/math.jpg">
          </a>
        </li>
        <li>
          <a href="plan.php?major=MechE" class="thumbnail">
            <img src="assets/img/majors/meche.jpg">
          </a>
        </li>
       <li>
          <a href="plan.php?major=Physics" class="thumbnail">
            <img src="assets/img/majors/physics.jpg">
          </a>
        </li>
      </ul>
    </div>
    <div class="row-fluid">
      <ul class="thumbnails" style="width:1200px;margin:auto;">
        <li>
          <a href="plan.php?major=SUE" class="thumbnail">
            <img src="assets/img/majors/sue.jpg">
          </a>
        </li>
      </ul>
    </div>
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

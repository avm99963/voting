<?php
require_once("core.php");

$i18n = new i18n("index");

$msg = "";
if (isset($_GET['msg']) && in_array($_GET['msg'], array("loginwrong", "empty", "logoutsuccess"))) {
  $msg = $i18n->msg("msg_".$_GET['msg']);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appname"]; ?></title>
  <meta charset="UTF-8">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Chrome for Android theme color -->
  <!--<meta name="theme-color" content="#2E3AA1">-->

  <link rel="stylesheet" href="bower_components/material-design-lite/material.min.css">
  <script src="bower_components/material-design-lite/material.min.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href='https://fonts.googleapis.com/css?family=Roboto:400,500,700,300' rel='stylesheet' type='text/css'>

  <link rel="stylesheet" href="css/stylemd.css">
  <link rel="stylesheet" href="css/index.css">

  <script src="bower_components/dialog-polyfill/dialog-polyfill.js"></script>
	<link rel="stylesheet" type="text/css" href="bower_components/dialog-polyfill/dialog-polyfill.css">

  <script src="js/index.js"></script>
</head>
<body class="mdl-color--green">
  <div class="login mdl-shadow--4dp">
    <h2><?=$conf["appname"]?></h2>
    <a class="loginoptioncontainer" href="dnieauth/">
      <div class="loginoption mdl-js-ripple-effect">
        <div class="icon"><i class="material-icons">credit_card</i></div>
        <div class="text">
          <span class="title"><?=$i18n->msg("loginDNIE")?></span><br>
          <span class="description"><?=$i18n->msg("loginDNIEexp")?></span>
        </div>
        <span class="mdl-ripple">
      </div>
    </a>
    <a class="loginoptioncontainer" href="logincode.php">
      <div class="loginoption mdl-js-ripple-effect">
        <div class="icon"><i class="material-icons">subject</i></div>
        <div class="text">
          <span class="title"><?=$i18n->msg("loginCode")?></span><br>
          <span class="description"><?=$i18n->msg("loginCodeExp")?></span>
        </div>
        <span class="mdl-ripple">
      </div>
    </a>
    <?php
    if (user::loggedin()) {
      ?>
      <a class="loginoptioncontainer" href="admin/">
        <div class="loginoption mdl-js-ripple-effect">
          <div class="icon"><i class="material-icons">dashboard</i></div>
          <div class="text">
            <span class="title"><?=$i18n->msg("loginAdmin")?></span><br>
            <span class="description"><?=$i18n->msg("loginAdminExp")?></span>
          </div>
          <span class="mdl-ripple">
        </div>
      </a>
      <?php
    }
    ?>
    <p style="margin-top: 16px;"><button id="aboutbtn" onclick="event.preventDefault();" class="mdl-button mdl-button--raised mdl-js-ripple-effect"><?=$i18n->msg("about")?><span class="mdl-ripple"></span></button></p>
  </div>
  <dialog class="mdl-dialog" id="about">
    <form action="csv.php" method="POST" enctype="multipart/form-data">
      <h4 class="mdl-dialog__title"><?=$i18n->msg("license")?></h4>
      <div class="mdl-dialog__content">
        <code>
          <p><?=$i18n->msg("copyright")?></p>
        </code>
      </div>
      <div class="mdl-dialog__actions">
        <button onclick="event.preventDefault(); document.querySelector('#about').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("ok")?></button>
      </div>
    </form>
  </dialog>
  <?php
  if (isset($msg) && !empty($msg)) {
    md::snackbar($msg);
  }
  ?>
</body>
</html>

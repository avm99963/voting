<?php
require_once(__DIR__."/../core.php");

$i18n = new i18n("adminlogin", 1);

$msg = "";
if (isset($_GET['msg']) && in_array($_GET['msg'], array("loginwrong", "empty", "logoutsuccess"))) {
  $msg = $i18n->msg("msg_".$_GET['msg']);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("login")?> – <?php echo $conf["appname"]; ?></title>
  <meta charset="UTF-8">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Chrome for Android theme color -->
  <!--<meta name="theme-color" content="#2E3AA1">-->

  <link rel="stylesheet" href="../bower_components/material-design-lite/material.min.css">
  <script src="../bower_components/material-design-lite/material.min.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href='https://fonts.googleapis.com/css?family=Roboto:400,500,700,300' rel='stylesheet' type='text/css'>

  <link rel="stylesheet" href="../css/stylemd.css">
  <link rel="stylesheet" href="../css/index.css">
</head>
<body class="mdl-color--green">
  <div class="login mdl-shadow--4dp" id="code">
    <h2><?=$i18n->msg("title", array($conf["appname"]))?></h2>
    <form action="dologin.php" method="POST" autocomplete="off" id="formulario">
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="text" name="username" id="username" autocomplete="off">
        <label class="mdl-textfield__label" for="username"><?=$i18n->msg("username")?></label>
      </div>
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off">
        <label class="mdl-textfield__label" for="password"><?=$i18n->msg("password")?></label>
      </div>
      <p><button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("login")?></button></p>
		</form>
  </div>
  <?php
  if (isset($msg) && !empty($msg)) {
    md::snackbar($msg);
  }
  ?>
</body>
</html>

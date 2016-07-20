<?php
require_once("core.php");

$i18n = new i18n("logincode");
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

  <link rel="stylesheet" href="bower_components/material-design-lite/material.min.css">
  <script src="bower_components/material-design-lite/material.min.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href='https://fonts.googleapis.com/css?family=Roboto:400,500,700,300' rel='stylesheet' type='text/css'>

  <link rel="stylesheet" href="css/stylemd.css">
  <link rel="stylesheet" href="css/index.css">
</head>
<body class="mdl-color--green">
  <div class="login mdl-shadow--4dp" id="code">
    <h2><?=$conf["appname"]?></h2>
    <form action="dologincode.php" method="POST" autocomplete="off" id="formulario">
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="password" name="code" id="code" autocomplete="off">
        <label class="mdl-textfield__label" for="code"><?=$i18n->msg("code")?></label>
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

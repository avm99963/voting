<?php
require_once("core.php");
$msg = "";
if (isset($_GET['msg'])) {
  if ($_GET['msg'] == "loginwrong")
    $msg = 'Usuario y/o contraseña incorrecto';
  if ($_GET['msg'] == "empty")
    $msg = 'Por favor, rellena todos los campos';
  if ($_GET['msg'] == "logoutsuccess")
    $msg = '¡Has cerrado la sesión correctamente! Ten un buen día :-)';
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
    <a class="loginoptioncontainer" href="logindnie.php">
      <div class="loginoption mdl-js-ripple-effect">
        <div class="icon"><i class="material-icons">credit_card</i></div>
        <div class="text">
          <span class="title">Iniciar sesión con DNI electrónico</span><br>
          <span class="description">Se requiere un lector de DNI electrónico.</span>
        </div>
        <span class="mdl-ripple">
      </div>
    </a>
    <a class="loginoptioncontainer" href="logincode.php">
      <div class="loginoption mdl-js-ripple-effect">
        <div class="icon"><i class="material-icons">subject</i></div>
        <div class="text">
          <span class="title">Iniciar sesión con código generado</span><br>
          <span class="description">En caso de no disponer de un lector de DNI electrónico.</span>
        </div>
        <span class="mdl-ripple">
      </div>
    </a>
    <p style="margin-top: 16px;"><button id="aboutbtn" onclick="event.preventDefault();" class="mdl-button mdl-button--raised mdl-js-ripple-effect">Sobre esta aplicación<span class="mdl-ripple"></span></button></p>
  </div>
  <dialog class="mdl-dialog" id="about">
    <form action="csv.php" method="POST" enctype="multipart/form-data">
      <h4 class="mdl-dialog__title">Licencia</h4>
      <div class="mdl-dialog__content">
        <code>
          <p>Copyright (c) 2016 Adrià Vilanova Martínez</p>
        </code>
      </div>
      <div class="mdl-dialog__actions">
        <button onclick="event.preventDefault(); document.querySelector('#about').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Aceptar</button>
      </div>
    </form>
  </dialog>
  <?php
  if (isset($_GET['msg'])) {
    md_snackbar($msg);
  }
  ?>
</body>
</html>

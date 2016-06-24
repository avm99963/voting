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
    <?php
		if (function_exists("loggedin") && loggedin()) {
      ?>
      <p>¡Hola <?php echo userdata('nombre'); ?>!</p>
      <?php
  		$whatif = mysqli_query($con, "SELECT * FROM reserva WHERE usuari = ".(INT)$_SESSION['id']);
  		if (mysqli_num_rows($whatif)) {
  			$link = "reservar.php";
        $linktext = "Modificar reservas";
  		} else {
  			$link = "welcome.php";
        $linktext = "Reservar horas";
  		}
  		?>
  		<p><?php if (isadmin()) {?> <a class="mdl-button md-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" href="dashboard.php">Panel de control<span class="mdl-ripple"></span></a><?php } else { ?><a class="mdl-button md-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" href="<?=$link?>"><?=$linktext?><span class="mdl-ripple"></span></a><?php } ?> <a class="mdl-button md-js-button mdl-button--raised mdl-js-ripple-effect" href="logout.php">Cerrar sesión<span class="mdl-ripple"></span></a></p>
      <?php
    } else {
      ?>
      <form action="login.php" method="POST" autocomplete="off" id="formulario">
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="id" id="id" autocomplete="off">
          <label class="mdl-textfield__label" for="id">Nombre</label>
        </div>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off">
          <label class="mdl-textfield__label" for="password">Contraseña</label>
        </div>
        <p><button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Iniciar sesión</button> <button id="aboutbtn" onclick="event.preventDefault();" class="mdl-button">Sobre esta aplicación</button></p>
  		</form>
      <?php
    }
    ?>
    <p>&copy; 2016 Adrià Vilanova Martínez</p>
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

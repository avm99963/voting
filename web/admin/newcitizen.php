<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

if (user::role() > 2) {
  die("You have no permissions");
}

$i18n = new i18n("admincensus", 1);

$dni = sanitizer::dbString($_POST['dni']);
$birthday = strtotime($_POST["birthday"]);

if (empty($_POST["name"]) || empty($_POST["surname"]) || empty($dni)) {
  header("Location: census.php?msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

$name = sanitizer::dbString($_POST['name']." ".$_POST["surname"]);

$uses = (int)$_POST["uses"];

if ($uses < 1) {
  header("Location: apikeys.php");
}

if (preg_match("/(\d{8})([a-zA-Z]{1})/", $dni) === false) {
  die("Incorrect DNI");
  exit();
}

if (mysqli_num_rows(mysqli_query($con, "SELECT id FROM generatedcodes WHERE dni = '".$dni."' AND status = 0"))) {
  header("Location: census.php?msg=activecode");
  exit();
}

do {
  $code = mysqli_real_escape_string($con, random::generateCode(16));
  $query = mysqli_query($con, "SELECT id FROM generatedcodes WHERE code = '".$code."'");
} while (mysqli_num_rows($query));

$sql6 = "INSERT INTO generatedcodes (code, name, dni, birthday, method, status, creation, usercreation, uses, usesdone) VALUES ('$code', '$name', '$dni', $birthday, 2, 0, ".time().", ".$_SESSION["id"].", ".$uses.", 0)";
if (mysqli_query($con,$sql6)) {
  $id = mysqli_insert_id($con);
} else {
  die ("[ERR_01]: Error adding citizen: " . mysqli_error($con) . "</p>");
}

$md_header_row_before = md::backBtn("census.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("census")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>
  <script src="../bower_components/qr-js/qr.min.js"></script>
  <style>
  #qrcode {
    display: block;
    margin-left: auto;
    margin-right: auto;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$name?></h2>
          <div class="mdl-shadow--2dp info"><?=$i18n->msg("codegenerated", array($code))?></div>
          <canvas id="qrcode"></canvas>
        </div>
      </div>
    </main>
  </div>
  <script>
  qr.canvas({
    canvas: document.querySelector("#qrcode"),
    value: "<?=generatedcodes::qrcode($code)?>",
    size: 8
  });
  </script>
</body>
</html>

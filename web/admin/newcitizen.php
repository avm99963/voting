<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

if (user::role() > 2) {
  die("You have no permissions");
}

$dni = sanitizer::dbString($_POST['dni']);
$birthday = strtotime($_POST["birthday"]);

if (empty($_POST["name"]) || empty($_POST["surname"]) || empty($dni)) {
  header("Location: census.php?msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

$name = sanitizer::dbString($_POST['name']." ".$_POST["surname"]);

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

$sql6 = "INSERT INTO generatedcodes (code, name, dni, birthday, method, status, creation, usercreation) VALUES ('$code', '$name', '$dni', $birthday, 2, 0, ".time().", ".$_SESSION["id"].")";
if (mysqli_query($con,$sql6)) {
  $id = mysqli_insert_id($con);
} else {
  die ("[ERR_01]: Error adding citizen: " . mysqli_error($con) . "</p>");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("census")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("census")?></h2>
          <p><?=$i18n->msg("censusexp")?></p>
          <h4><?=$i18n->msg("searchcitizen")?></h4>
          <form action="searchcitizen.php" method="GET">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off">
              <label class="mdl-textfield__label" for="name"><?=$i18n->msg("name")?></label>
            </div>
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect"><?=$i18n->msg("search")?></button>
          </form>
        </div>
      </div>
    </main>
  </div>
  <?php
  if (user::role() < 3) {
    ?>
    <dialog class="mdl-dialog" id="adduser">
      <form action="newcitizen.php" method="POST" autocomplete="off">
        <h4 class="mdl-dialog__title"><?=$i18n->msg("addcitizen_title")?></h4>
        <div class="mdl-dialog__content">
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off">
            <label class="mdl-textfield__label" for="name"><?=$i18n->msg("name")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="surname" id="surname" autocomplete="off">
            <label class="mdl-textfield__label" for="surname"><?=$i18n->msg("surname")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="dni" id="dni" pattern="(\d{8})([a-zA-Z]{1})" maxlength="9" autocomplete="off">
            <label class="mdl-textfield__label" for="dni"><?=$i18n->msg("dni")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="date" name="birthday" id="birthday" autocomplete="off">
            <label class="mdl-textfield__label always-focused" for="birthday"><?=$i18n->msg("birthday")?></label>
          </div>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("add")?></button>
          <button onclick="event.preventDefault(); document.querySelector('#adduser').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
        </div>
      </form>
    </dialog>
    <?php
  }

  md::msg(array("citizenadded", "citizennew", "citizendelete", "empty", "usernametaken"));
  ?>
</body>
</html>

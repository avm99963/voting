<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("admincensus", 1);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("census")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <script src="../js/census.js"></script>

  <style>
  .adduser {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <?php if (user::role() < 3) { ?>
        <button class="adduser mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">person_add</i><span class="mdl-ripple"></span></button>
      <?php } ?>
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
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="number" min="1" name="uses" id="uses" autocomplete="off">
            <label class="mdl-textfield__label" for="uses"><?=$i18n->msg("usesforcode")?></label>
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

  md::msg(array("citizenadded", "citizennew", "citizendelete", "empty", "usernametaken", "activecode"));
  ?>
</body>
</html>

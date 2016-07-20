<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminindex", 1);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("dashboard")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("welcome")?></h2>
      		<?=$i18n->msg("welcomeExp", array(user::userData('name')))?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

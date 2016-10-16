<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminresults", 1);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("results")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <style>
  .addresult {
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
      <?php if (user::role() == 0) { ?>
        <button class="addresult mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
      <?php } ?>
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("results")?></h2>
          <h5><?=$i18n->msg("suggestedactions")?></h5>
          <?php
          $query = mysqli_query($con, "SELECT votings.id, votings.name FROM votings LEFT OUTER JOIN voting_defaultresults ON votings.id = voting_defaultresults.voting WHERE voting_defaultresults.id IS NULL AND votings.status = 1 AND votings.dateends < ".(int)time());

          $generateresults = array();

          if (mysqli_num_rows($query)) {
            while ($row = mysqli_fetch_assoc($query)) {
              $generateresults[] = $row;
            }
          }

          if (!count($generateresults)) {
            echo "<p>".$i18n->msg("nosuggestedactions")."</p>";
          } else {
            ?>
            <ul>
              <?php
              foreach ($generateresults as $voting) {
                ?>
                <li><?=$i18n->msg("generateresult", array($voting["name"]))?> <?php if (user::role() == 0) { ?><a class="mdl-button mdl-js-button mdl-button--icon" href="generateresults.php?id=<?=$voting["id"]?>"><i class="material-icons mdl-color-text--green">done</i></a><?php } ?></li>
                <?php
              }
              ?>
            </ul>
            <?php
          }
          ?>
          <h5><?=$i18n->msg("generatedresults")?></h5>

        </div>
      </div>
    </main>
  </div>
  <?php
  if (user::role() < 3) {
    ?>
    <dialog class="mdl-dialog" id="addresult">

    </dialog>
    <?php
  }

  md::msg(array("resultgenerated", "resultnotgenerated"));
  ?>
</body>
</html>

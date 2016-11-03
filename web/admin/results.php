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

  <link rel="stylesheet" href="../lib/mdl-ext/mdl-ext.min.css">
  <script src="../lib/mdl-ext/mdl-ext.min.js"></script>

  <script src="../js/results.js"></script>

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
  if (user::role() == user::ADMIN) {
    ?>
    <dialog class="mdl-dialog" id="addresult">
      <form action="newresult.php" method="POST" autocomplete="off">
        <h4 class="mdl-dialog__title"><?=$i18n->msg("newresult_title")?></h4>
        <div class="mdl-dialog__content">
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="voting" id="voting" class="mdlext-selectfield__select">
              <option value=""></option>
              <?php
              $query2 = mysqli_query($con, "SELECT votings.id, votings.name FROM votings LEFT OUTER JOIN voting_defaultresults ON votings.id = voting_defaultresults.voting WHERE voting_defaultresults.id IS NOT NULL AND votings.status = 1 AND votings.dateends < ".(int)time());

              if (mysqli_num_rows($query2)) {
                while ($row2 = mysqli_fetch_assoc($query2)) {
                  echo '<option value="'.$row2["id"].'">'.$row2["name"].'</option>';
                }
              }
              ?>
            </select>
            <label for="type" class="mdlext-selectfield__label"><?=$i18n->msg("voting")?></label>
          </div>
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="method" id="method" class="mdlext-selectfield__select">
              <option value=""></option>
              <?php
              foreach (results::$methods as $i) {
                echo '<option value="'.$i.'">'.$i18n->msg("resultsmethod_".$i).'</option>';
              }
              ?>
            </select>
            <label for="type" class="mdlext-selectfield__label"><?=$i18n->msg("methods")?></label>
          </div>
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="blankballot" id="blankballot" class="mdlext-selectfield__select">
              <option value=""></option>
              <?php
              $query3 = mysqli_query($con, "SELECT * FROM voting_ballots WHERE voting = ".$id);

              if (mysqli_num_rows($query3)) {
                while ($row3 = mysqli_fetch_assoc($query3)) {
                  echo '<option value="'.$row3["id"].'">'.$row3["name"].'</option>';
                }
              }
              ?>
            </select>
            <label for="type" class="mdlext-selectfield__label"><?=$i18n->msg("blankballot")?></label>
          </div>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off">
            <label class="mdl-textfield__label" for="name"><?=$i18n->msg("name")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <textarea class="mdl-textfield__input" name="description" rows="3" id="description" autocomplete="off"></textarea>
            <label class="mdl-textfield__label" for="description"><?=$i18n->msg("description")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <textarea class="mdl-textfield__input" name="votingdescription" rows="3" id="votingdescription" autocomplete="off"></textarea>
            <label class="mdl-textfield__label" for="votingdescription"><?=$i18n->msg("votingdescription")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="datetime-local" name="datebegins" id="datebegins" autocomplete="off"></textarea>
            <label class="mdl-textfield__label always-focused" for="datebegins"><?=$i18n->msg("datebegins")?></label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="datetime-local" name="dateends" id="dateends" autocomplete="off"></textarea>
            <label class="mdl-textfield__label always-focused" for="dateends"><?=$i18n->msg("dateends")?></label>
          </div>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("add")?></button>
          <button onclick="event.preventDefault(); document.querySelector('#addresult').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
        </div>
      </form>
    </dialog>
    <?php
  }

  md::msg(array("resultgenerated", "resultnotgenerated"));
  ?>
</body>
</html>

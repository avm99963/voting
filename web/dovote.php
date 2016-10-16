<?php
require_once(__DIR__."/core.php");

if (!citizen::loggedIn()) {
  header('Location: index.php');
  exit();
}

$i18n = new i18n("dashboard");

if (!isset($_POST["ballot"]) || !isset($_POST["voting"])) {
  header("Location: dashboard.php");
  exit();
}

$voting = voting::votingData("name", (int)$_POST["voting"]);

$vote = voting::doVote($_POST["voting"], explode(",", $_POST["ballot"]));

if (isset($vote["error"])) {
  die("Error ".$vote["error"]." has occurred while submitting your ballot.");
}

$md_header_row_before = md::backBtn("voting.php?id=".(int)$_POST["voting"]);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$voting?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/includes/citizenhead.php"); ?>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/includes/citizenmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$voting?></h2>
          <div class="info mdl-shadow--2dp"><?=$i18n->msg("votingsubmitted".(count($vote["ballots"]) == 1 ? "" : "_plural"))?></div>
          <p><?=$i18n->msg("verifyauthenticity".(count($vote["ballots"]) == 1 ? "" : "_plural"))?></p>
          <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
            <tr><th class="mdl-data-table__cell--non-numeric">Ballot</th><th class="mdl-data-table__cell--non-numeric">Salt</th></tr>
            <?php
            foreach ($vote["ballots"] as $ballot) {
              ?>
              <tr><td class="mdl-data-table__cell--non-numeric"><?=$ballot["name"]?></td><td class="mdl-data-table__cell--non-numeric"><code><?=$ballot["salt"]?></code></td></tr>
              <?php
            }
            ?>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

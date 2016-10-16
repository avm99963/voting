<?php
require_once(__DIR__."/core.php");

if (!citizen::loggedIn()) {
  header('Location: index.php');
  exit();
}

$i18n = new i18n("dashboard");

if (!isset($_GET["id"])) {
  header("Location: dashboard.php");
  exit();
}

$id = (int)$_GET["id"];

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id." AND status = 1");

if (!mysqli_num_rows($query)) {
  header("Location: dashboard.php");
  exit();
}

$row = mysqli_fetch_assoc($query);

if ($votestatus = voting::canVote($row["id"]) < 1) {
  header("Location: voting.php?id=".$row["id"]."&msg=votestatus_".$votestatus);
  exit();
}

$md_header_row_before = md::backBtn("voting.php?id=".$row["id"]);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$row["name"]?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/includes/citizenhead.php"); ?>

  <script>var maxvotingballots = <?=($row["maxvotingballots"])?>, variable = <?=($row["variable"] == 1 ? "true" : "false")?>;</script>
  <script src="js/vote.js"></script>
  <style>
  .votebtn {
    background-color: rgba(255, 255, 255, 0.15);
  }

  .ballot.dark-text .votebtn {
    background-color: rgba(0, 0, 0, 0.15)!important;
  }

  .ballot.dark-text .votebtn:hover {
    background-color: rgba(0, 0, 0, 0.1)!important;
  }

  .mdl-switch {
    margin-top: 4px;
    width: auto;
  }

  .multivote {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/includes/citizenmdnav.php"); ?>
    <main class="mdl-layout__content">
      <?php
      if ($row["maxvotingballots"] > 1) {
        ?>
        <button id="multivote" class="multivote mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">send</i><span class="mdl-ripple"></span></button>
        <?php
      }
      ?>
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <?php
          if (empty($row["customhtml"])) {
            ?>
            <h2><?=$i18n->msg("votetitle")?> - <?=$row["name"]?></h2>
            <p><?=$row["votingdescription"]?></p>
            <div class="mdl-grid">
              <?php
              $query2 = mysqli_query($con, "SELECT * FROM voting_ballots WHERE voting = ".$id);
              if (mysqli_num_rows($query2)) {
                while ($ballot = mysqli_fetch_assoc($query2)) {
                  $hsl = hex2hsl($ballot["color"]);
                  ?>
                  <div class="mdl-cell mdl-cell--4-col ballot mdl-shadow--2dp<?=($hsl[2] > 0.5 ? " dark-text" : "")?>" style="background-color: #<?=$ballot["color"]?>;">
                    <div class="title mdl-card--expand">
                      <h4>
                        <?=$ballot["name"]?>
                      </h4>
                    </div>
                    <div class="actions mdl-card--border">
                      <?php
                      if ($row["maxvotingballots"] > 1) {
                        ?>
                        <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="switch-<?=$ballot["id"]?>">
                          <input type="checkbox" id="switch-<?=$ballot["id"]?>" data-ballotid="<?=$ballot["id"]?>" class="mdl-switch__input">
                        </label>
                        <?php
                      } else {
                        ?>
                        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect votebtn" onclick="vote(<?=$ballot["id"]?>);">
                          <?=$i18n->msg("vote")?>
                        </a>
                        <?php
                      }
                      ?>
                      <div class="alignright">
                        <?php if (!empty($ballot["description"])) { ?><a href="javascript:dynDialog.load('ajax/ballot.php?id=<?=$ballot["id"]?>');" class="mdl-button mdl-js--button mdl-button--icon"><i class="material-icons">expand_more</i></a><?php } ?>
                      </div>
                    </div>
                  </div>
                  <?php
                }
              }
              ?>
              </div>
            <?php
          } else {
            echo $row["customhtml"];
          }
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

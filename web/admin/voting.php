<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

$id = (int)$_GET["id"];

if (empty($id)) {
  header("Location: votings.php");
  exit();
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
  exit();
}

$row = mysqli_fetch_assoc($query);

$md_header_row_before = md::backBtn("votings.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$row["name"]?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <link rel="stylesheet" href="../lib/mdl-ext/mdl-ext.min.css">
  <script src="../lib/mdl-ext/mdl-ext.min.js"></script>

  <script src="../js/voting.js"></script>

  <style>
  .addballot {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }

  #actions {
    margin-top: 16px;
    margin-left: 16px;
    margin-bottom: 16px;
    float: right;
  }

  .menulink {
    text-decoration: none;
  }

  .ballot {
    position: relative;
    height: 200px;
  }

  .ballot .title {
    padding: 16px;
  }
  .ballot .title h4 {
    margin-top: 0;
  }
  .ballot .actions {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    position: absolute;
    bottom: 0;
    padding: 8px;
    width: Calc(100% - 16px);
  }
  .ballot.dark-text .actions {
    border-top: 1px solid rgba(0, 0, 0, 0.2)!important;
  }
  .ballot .actions .material-icons {
    padding-right: 10px;
  }
  .ballot .title,
  .ballot .actions,
  .ballot .actions .mdl-button {
    color: #fff;
  }

  .ballot.dark-text .title,
  .ballot.dark-text .actions,
  .ballot.dark-text .actions .mdl-button {
    color: rgba(0, 0, 0, .87)!important;
  }

  .ballot .actions .alignright {
    float: right;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <?php if (user::role() == 0 && $row["status"] == 0) { ?>
        <button class="addballot mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">description</i><span class="mdl-ripple"></span></button>
      <?php } ?>
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <div id="actions">
            <?php
            if ($row["status"] == 0) {
              ?>
              <a<?=(user::role() == 0 ? " href=\"javascript:dynDialog.load('ajax/publishvoting.php?id=$id');\"" : "")?> class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect"<?=(user::role() != 0 ? " disabled" : "")?>><?=$i18n->msg("publish")?></a>
              <?php
            } else {
              ?>
              <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect" disabled><?=$i18n->msg("published")?></button>
              <?php
            }
            ?>
            <button id="menu" class="mdl-button mdl-js-button mdl-button--icon">
              <i class="material-icons">more_vert</i>
            </button>
          </div>
          <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="menu">
            <?php if (user::role() == 0) { ?><a class="menulink" href="javascript:dynDialog.load('ajax/editvoting.php?id=<?=$id?>');"><?php } ?><li<?=(user::role() != 0 ? " disabled" : "")?> class="mdl-menu__item"><?=($row["status"] == 0 ? $i18n->msg("editvoting") : $i18n->msg("viewvotingdetails"))?></li><?php if (user::role() == 0) { ?></a><?php } ?>
            <?php if (user::role() == 0) { ?><a class="menulink" href="filtervoting.php?id=<?=$id?>"><?php } ?><li<?=(user::role() != 0 ? " disabled" : "")?> class="mdl-menu__item"><?=($row["status"] == 0 ? $i18n->msg("managefilters") : $i18n->msg("viewfilters"))?></li><?php if (user::role() == 0) { ?></a><?php } ?>
            <?php if (user::role() < 2) { ?><a class="menulink" href="apikeys.php?id=<?=$id?>"><?php } ?><li<?=(user::role() > 1 ? " disabled" : "")?> class="mdl-menu__item"><?=$i18n->msg("manageapikeys")?></li><?php if (user::role() < 2) { ?></a><?php } ?>
            <?php if (user::role() == 0) { ?><a class="menulink" href="javascript:dynDialog.load('ajax/customhtml.php?id=<?=$id?>');"><?php } ?><li<?=(user::role() != 0 ? " disabled" : "")?> class="mdl-menu__item"><?=$i18n->msg("editcustomhtml")?></li><?php if (user::role() == 0) { ?></a><?php } ?>
            <?php if (user::role() == 0) { ?><a class="menulink" href="javascript:dynDialog.load('ajax/votingscenario.php?id=<?=$id?>');"><?php } ?><li<?=(user::role() != 0 ? " disabled" : "")?> class="mdl-menu__item"><?=$i18n->msg("votingscenario")?></li><?php if (user::role() == 0) { ?></a><?php } ?>
            <?php if (user::role() == 0 && $row["status"] == 0) { ?><a class="menulink" href="javascript:dynDialog.load('ajax/deletevoting.php?id=<?=$id?>');"><?php } ?><li<?=((user::role() != 0 || $row["status"] != 0) ? " disabled" : "")?> class="mdl-menu__item"><?=$i18n->msg("deletevoting")?></li><?php if (user::role() == 0 && $row["status"] == 0) { ?></a><?php } ?>
          </ul>
          <h2><?=$row["name"]?></h2>
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
                    <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" disabled>
                      <?=$i18n->msg("vote")?>
                    </a>
                    <div class="alignright">
                      <?php if (user::role() == 0) { ?><a href="javascript:dynDialog.load('ajax/editballot.php?id=<?=$ballot["id"]?>');" class="mdl-button mdl-js--button mdl-button--icon"><?php } ?><i class="material-icons">edit</i><?php if (user::role() == 0) { ?></a><?php } ?>
                      <?php if (user::role() == 0) { ?><a href="javascript:dynDialog.load('ajax/deleteballot.php?id=<?=$ballot["id"]?>');" class="mdl-button mdl-js--button mdl-button--icon"><?php } ?><i class="material-icons">delete</i><?php if (user::role() == 0) { ?></a><?php } ?>
                    </div>
                  </div>
                </div>
                <?php
              }
            } else {
              ?>
              <p><i><?=$i18n->msg("noballots")?></i></p>
              <?php
            }
            ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <?php
  if (user::role() == 0) {
    ?>
    <dialog class="mdl-dialog" id="addballot">
      <form action="newballot.php" method="POST" autocomplete="off">
        <input type="hidden" name="voting" value="<?=$id?>">
        <h4 class="mdl-dialog__title"><?=$i18n->msg("addballot_title")?></h4>
        <div class="mdl-dialog__content">
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
            <input class="mdl-textfield__input" type="color" name="color" id="color" autocomplete="off" value="#333333">
            <label class="mdl-textfield__label" for="color"><?=$i18n->msg("color")?></label>
          </div>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("add")?></button>
          <button onclick="event.preventDefault(); document.querySelector('#addballot').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
        </div>
      </form>
    </dialog>
    <?php
  }

  md::msg(array("votingnew", "votingpublished", "empty", "datediff", "ballotadded", "ballotnew", "ballotdelete", "customhtmlsuccess"));
  ?>
</body>
</html>

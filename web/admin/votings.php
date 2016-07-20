<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("votings")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <link rel="stylesheet" href="../lib/mdl-ext/mdl-ext.min.css">
  <script src="../lib/mdl-ext/mdl-ext.min.js"></script>

  <script src="../js/votings.js"></script>

  <style>
  .addvoting, .filtervoting.large {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }

  .filtervoting.small {
    position:fixed;
    bottom: 80px;
    right: 25px;
  }

  .always-focused {
    font-size: 12px;
    top: 4px;
    visibility: visible;
  }

  .mdl-list__item {
    cursor: pointer;
  }

  .votingcontainer {
    text-decoration: none!important;
    color: black!important;
  }

  .voting {
    position: relative;
    display: block;
    width: Calc(100% - 10px);
    margin-bottom: 5px;
    overflow-x: hidden;
    overflow-y: hidden;
    padding: 5px;
  }

  .voting:hover {
    background-color: #EEE;
  }

  .voting .icon {
    float:left;
    margin-right: 8px;
  }

  .voting .material-icons {
    font-size: 40px;
  }

  .voting .title {
    font-weight: bold;
  }

  .voting .description {
    color: rgba(0, 0, 0, .54);
  }

  .draft { border-left: 4px solid #BDBDBD; }
  .published { border-left: 4px solid #00E5FF; }
  .running { border-left: 4px solid #00E676; }
  .finished { border-left: 4px solid #FF3D00; }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <?php if (user::role() == 0) { ?>
        <button class="addvoting mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
        <button class="filtervoting small mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">filter_list</i></button>
      <?php } else { ?>
        <button class="filtervoting large mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">filter_list</i><span class="mdl-ripple"></span></button>
      <?php } ?>
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("votings")?></h2>
          <?php
          $filtering = (isset($_GET["st0"]) xor isset($_GET["st1"]));

          if ($filtering) {
            if (isset($_GET["st0"])) {
              $where = " WHERE status = 0";
            } else {
              $where = " WHERE status = 1";
            }
          } else {
            $where = "";
          }

          $query = mysqli_query($con, "SELECT * FROM votings".$where);
          if (mysqli_num_rows($query)) {
            $now = time();
            while ($row = mysqli_fetch_assoc($query)) {
              if ($row["status"] == 0) {
                $class = "draft";
              } else {
                if ($now < $row["datebegins"]) {
                  $class = "published";
                } elseif ($now < $row["dateends"]) {
                  $class = "running";
                } else {
                  $class = "finished";
                }
              }
              ?>
              <a class="votingcontainer" href="voting.php?id=<?=$row["id"]?>">
                <div class="voting <?=$class?> mdl-js-ripple-effect">
                  <div class="text">
                    <span class="title"><?=$row["name"]?></span><br>
                    <span class="description"><?=$row["description"]?></span>
                  </div>
                  <span class="mdl-ripple">
                </div>
              </a>
              <?php
            }
          } else {
            ?>
            <p><i><?=$i18n->msg("novotings")?></i></p>
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
    <dialog class="mdl-dialog" id="addvoting">
      <form action="newvoting.php" method="POST" autocomplete="off">
        <h4 class="mdl-dialog__title"><?=$i18n->msg("add_title")?></h4>
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
          <button onclick="event.preventDefault(); document.querySelector('#addvoting').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
        </div>
      </form>
    </dialog>
    <?php
  }
  ?>
  <dialog class="mdl-dialog" id="filtervoting">
    <form action="votings.php" method="GET" autocomplete="off">
      <h4 class="mdl-dialog__title"><?=$i18n->msg("filter_title")?></h4>
      <div class="mdl-dialog__content">
        <h5><?=$i18n->msg("status")?></h5>
        <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="st0">
          <input type="checkbox" value="show" id="st0" name="st0" class="mdl-checkbox__input" <?=((!$filtering || isset($_GET["st0"])) ? "checked" : "")?>>
          <span class="mdl-checkbox__label"><?=$i18n->msg("status_0")?></span>
        </label>
        <br>
        <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="st1">
          <input type="checkbox" value="show" id="st1" name="st1" class="mdl-checkbox__input" <?=((!$filtering || isset($_GET["st1"])) ? "checked" : "")?>>
          <span class="mdl-checkbox__label"><?=$i18n->msg("status_1")?></span>
        </label>
        <br>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("filter")?></button>
        <button onclick="event.preventDefault(); document.querySelector('#filtervoting').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
      </div>
    </form>
  </dialog>
  <?php
  md::msg(array("votingadded", "votingnew", "votingdelete", "empty", "datediff"));
  ?>
</body>
</html>

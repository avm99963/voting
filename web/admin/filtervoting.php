<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

if (user::role() != 0) {
  die("You don't have permissions.");
}

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

$filters = json_decode($row["filters"], true);

$md_header_row_before = md::backBtn("voting.php?id=".$id);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("managefilters")?> – <?=$row["name"]?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <script src="../js/filtervoting.js"></script>

  <style>
  .mdl-tabs__panel {
    padding: 16px 16px 0 16px;
  }

  .age {
    width: 50px;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("managefilters")?> – <?=$row["name"]?></h2>
          <!--<p><?=$i18n->msg("managefilters_intro")?></p>
          <p><?=$i18n->msg("managefilters_intro2")?></p>-->
          <form action="dofiltervoting.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="voting" value="<?=$id?>">
            <div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
              <div class="mdl-tabs__tab-bar">
                <a href="#whitelist" class="mdl-tabs__tab is-active">Whitelist</a>
                <a href="#blaclist" class="mdl-tabs__tab">Blacklist</a>
                <a href="#age" class="mdl-tabs__tab">Age filter</a>
              </div>

              <div class="mdl-tabs__panel is-active" id="whitelist">
                <p>
                  <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="whitelist_enabled">
                    <input type="checkbox" id="whitelist_enabled" name="whitelist_enabled" value="enabled" class="mdl-switch__input"<?=($filters["whitelist"]["enabled"] == 1 ? " checked" : ($filters["blacklist"]["enabled"] == 1 ? " disabled" : ""))?><?=($row["status"] != 0 ? " disabled" : "")?>>
                    <span class="mdl-switch__label"><?=$i18n->msg("enablewhitelist")?></span>
                  </label>
                </p>
                <div id="whitelist_hidden" style="<?=($filters["whitelist"]["enabled"] == 0 ? "display: none;" : "")?>">
                  <p><?=$i18n->msg("selectwhitelist")?></p>
                  <p><input type="file" name="whitelist_file"<?=($row["status"] != 0 ? " disabled" : "")?>></p>
                  <p><?=$i18n->msg("whitelistexp")?></p>
                  <?php if ($filters["whitelist"]["enabled"]) { ?><p><a class="mdl-button mdl-js-button mdl-js-ripple-effect" href="download.php?well=whitelist&id=<?=$row["id"]?>" target="_blank"><?=$i18n->msg("downloadwhitelist")?></a></p><?php } ?>
                </div>
              </div>
              <div class="mdl-tabs__panel" id="blaclist">
                <p>
                  <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="blacklist_enabled">
                    <input type="checkbox" id="blacklist_enabled" name="blacklist_enabled" value="enabled" class="mdl-switch__input"<?=($filters["blacklist"]["enabled"] == 1 ? " checked" : ($filters["whitelist"]["enabled"] == 1 ? " disabled" : ""))?><?=($row["status"] != 0 ? " disabled" : "")?>>
                    <span class="mdl-switch__label"><?=$i18n->msg("enableblacklist")?></span>
                  </label>
                </p>
                <div id="blacklist_hidden" style="<?=($filters["blacklist"]["enabled"] == 0 ? "display: none;" : "")?>">
                  <p><?=$i18n->msg("selectblacklist")?></p>
                  <p><input type="file" name="blacklist_file"<?=($row["status"] != 0 ? " disabled" : "")?>></p>
                  <p><?=$i18n->msg("blacklistexp")?></p>
                  <?php if ($filters["blacklist"]["enabled"]) { ?><p><a class="mdl-button mdl-js-button mdl-js-ripple-effect" href="download.php?well=blacklist&id=<?=$row["id"]?>" target="_blank"><?=$i18n->msg("downloadblacklist")?></a></p><?php } ?>
                </div>
              </div>
              <div class="mdl-tabs__panel" id="age">
                <p>
                  <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="age_enabled">
                    <input type="checkbox" id="age_enabled" name="age_enabled" value="enabled" class="mdl-switch__input"<?=($filters["age"]["enabled"] == 1 ? " checked" : "")?><?=($row["status"] != 0 ? " disabled" : "")?>>
                    <span class="mdl-switch__label"><?=$i18n->msg("enableagefilter")?></span>
                  </label>
                </p>
                <div id="age_hidden" style="<?=($filters["age"]["enabled"] == 0 ? "display: none;" : "")?>">
                  <span><?=$i18n->msg("age1")?></span>
                  <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label age">
                    <input class="mdl-textfield__input" type="number" name="age" id="age" autocomplete="off"<?=($filters["age"]["enabled"] == 1 ? " value=\"".(int)$filters["age"]["age"]."\"" : "")?><?=($row["status"] != 0 ? " readonly" : "")?>>
                    <label class="mdl-textfield__label" for="age"></label>
                  </div>
                  <span><?=$i18n->msg("age2")?></span>
                </div>
              </div>
            </div>
            <hr>
            <p><button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"<?=($row["status"] != 0 ? " disabled" : "")?>><?=$i18n->msg("save")?></button></p>
          </form>
        </div>
      </div>
    </main>
  </div>
  <?php
  md::msg(array("empty", "filteredit"));
  ?>
</body>
</html>

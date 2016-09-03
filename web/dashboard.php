<?php
require_once(__DIR__."/core.php");

if (!citizen::loggedIn()) {
  header('Location: index.php');
  exit();
}

$i18n = new i18n("dashboard");

$time = time();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$i18n->msg("votings")?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/includes/citizenhead.php"); ?>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/includes/citizenmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$i18n->msg("votings")?></h2>
      		<p><?=$i18n->msg("votingsExp", array(citizen::userData('name')))?></p>
          <h4><?=$i18n->msg("upcomingvotings")?></h4>
          <?php
          $query = mysqli_query($con, "SELECT * FROM votings WHERE status = 1 AND datebegins > ".$time);
          if (!mysqli_num_rows($query)) {
            ?>
            <p><?=$i18n->msg("nopublishedvotings")?></p>
            <?php
          } else {
            while ($row = mysqli_fetch_assoc($query)) {
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
          }
          ?>
          <h4><?=$i18n->msg("activevotings")?></h4>
          <?php
          $query = mysqli_query($con, "SELECT * FROM votings WHERE status = 1 AND datebegins <= ".$time." AND dateends >= ".$time);
          if (!mysqli_num_rows($query)) {
            ?>
            <p><?=$i18n->msg("nopublishedvotings")?></p>
            <?php
          } else {
            while ($row = mysqli_fetch_assoc($query)) {
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
          }
          ?>
          <h4><?=$i18n->msg("pastvotings")?></h4>
          <?php
          $query = mysqli_query($con, "SELECT * FROM votings WHERE status = 1 AND dateends < ".$time);
          if (!mysqli_num_rows($query)) {
            ?>
            <p><?=$i18n->msg("nopublishedvotings")?></p>
            <?php
          } else {
            while ($row = mysqli_fetch_assoc($query)) {
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
          }
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

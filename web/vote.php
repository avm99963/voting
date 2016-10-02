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

  <style>
  #actions {
    margin-top: 16px;
    margin-left: 16px;
    margin-bottom: 16px;
    float: right;
  }
  </style>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/includes/citizenmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <?php
          if (empty($row["customhtml"])) {
            ?>
            <h2><?=$i18n->msg("votetitle")?> - <?=$row["name"]?></h2>
            <p><?=$row["votingdescription"]?></p>
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

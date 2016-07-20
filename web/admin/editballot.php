<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

if (user::role() != 0) {
  header("Location: index.php");
  exit();
}

$id = (int)$_GET["id"];

if (empty($id)) {
  header("Location: votings.php");
  exit();
}

$query = mysqli_query($con, "SELECT * FROM voting_ballots WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This ballot does not exist");
  exit();
}

$row = mysqli_fetch_assoc($query);

$md_header_row_before = md::backBtn("voting.php?id=".$row["voting"]);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?=$row["name"]?> – <?php echo $conf["appname"]; ?></title>
  <?php include(__DIR__."/../includes/adminhead.php"); ?>

  <link rel="stylesheet" href="../lib/mdl-ext/mdl-ext.min.css">
  <script src="../lib/mdl-ext/mdl-ext.min.js"></script>
</head>
<body class="mdl-color--green">
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php require(__DIR__."/../includes/adminmdnav.php"); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2><?=$row["name"]?></h2>
          <form action="newballot.php" method="POST" autocomplete="off">
            <input type="hidden" name="ballot" value="<?=$id?>">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" value="<?=$row["name"]?>">
              <label class="mdl-textfield__label" for="name"><?=$i18n->msg("name")?></label>
            </div>
            <br>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <textarea class="mdl-textfield__input" name="description" rows="3" id="description" autocomplete="off"><?=$row["description"]?></textarea>
              <label class="mdl-textfield__label" for="description"><?=$i18n->msg("description")?></label>
            </div>
            <br>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="color" name="color" id="color" autocomplete="off" value="#<?=$row["color"]?>">
              <label class="mdl-textfield__label always-focused" for="color"><?=$i18n->msg("color")?></label>
            </div>
            <p><button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("edit")?></button></p>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
  <?php
  md::msg(array("votingnew", "empty", "datediff"));
  ?>

</body>
</html>

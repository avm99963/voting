<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminusers", 1);

$query = mysqli_query($con, "SELECT * FROM users WHERE id = ".(int)$_GET['id']) or die("<div class='alert-danger'>".mysqli_error()."</div>");
if (!mysqli_num_rows($query)) {
  die("This user doesn't exist.");
}
$row = mysqli_fetch_assoc($query);

$md_header_row_before = md::backBtn("users.php");
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
          <?php
      		echo "<p><b>".$i18n->msg("id")."</b>: ".$row['id']."</p><p><b>".$i18n->msg("username")."</b>: ".$row['username']."</p><p><b>".$i18n->msg("realname")."</b>: ".$row['name']."</p><p><b>".$i18n->msg("type")."</b>: ".$i18n->msg("type_".(int)$row["type"])."</p><p><a href=\"javascript:dynDialog.load('ajax/edituser.php?id=".$row['id']."');\" class='mdl-button mdl-color-text--orange mdl-js-ripple-effect'>".$i18n->msg("edituser")."<span class='mdl-ripple'></span></a> <a style='color:red;' href=\"javascript:dynDialog.load('ajax/deleteuser.php?id=".$row['id']."');\" class='mdl-button mdl-color-text--red mdl-js-ripple-effect'>".$i18n->msg("deleteuser")."<span class='mdl-ripple'></span></a></p>";
      		?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

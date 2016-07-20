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

$id = (int)$_REQUEST["id"];

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
          <?php
      		if (isset($_POST["sent"]) && $_POST['sent'] == "1") {
      			$sql = "DELETE FROM voting_ballots WHERE id = '".(INT)$_POST['id']."' LIMIT 1";
    				if (mysqli_query($con, $sql)) {
  	  				header("Location: voting.php?id=".$row["voting"]."&msg=ballotdelete");
    	 			} else {
    					die ("[err00] Error deleting ballot: ".mysqli_error($con));
    				}
      		} else {
        		?>
        		<p><?=$i18n->msg("delete_areyousure_object", array($row["name"]))?></p>
            <p><form method="POST" action="deleteballot.php"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="sent" value="1"><button type="submit" class="mdl-button md-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("yes")?><span class="mdl-ripple"></span></button> <a class="mdl-button md-js-button mdl-button--raised mdl-js-ripple-effect" href="voting.php?id=<?=$row["voting"]?>" class="button-link"><?=$i18n->msg("no")?><span class="mdl-ripple"></span></a></form></p>
        		<?php
      		}
      		?>
        </div>
      </div>
    </main>
  </div>
  <?php
  md::msg(array("votingnew", "empty", "datediff"));
  ?>

</body>
</html>

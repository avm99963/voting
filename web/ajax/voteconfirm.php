<?php
require_once(__DIR__."/../core.php");

if (!citizen::loggedIn()) {
  die("Resource not found");
}

$i18n = new i18n("dashboard", 1);

if (!isset($_GET["id"])) {
  die("Resource not found");
}

$ids = explode(",", $_GET["id"]);

if (count($ids) == 1 && $ids[0] == "") {
  templates::dynDialogError($i18n->msg("noballots"));
}

$ballots = array();
$voting = -1;

foreach ($ids as $id) {
  $query = mysqli_query($con, "SELECT * FROM voting_ballots WHERE id = ".(int)$id);

  if (!mysqli_num_rows($query)) {
    die("Resource not found");
  }

  $row = mysqli_fetch_assoc($query);

  if ($voting === -1) {
    $voting = $row["voting"];
  } elseif ($voting != $row["voting"]) {
    die("Resource not found");
  }

  $ballots[] = array("name" => $row["name"], "color" => $row["color"]);
}

if (voting::canVote($voting) < 1) {
  die("Resource not found");
}

$maxvotes = voting::votingData("maxvotingballots", $voting);
$variable = voting::votingData("variable", $voting);

if ($variable == 1 && count($ballots) != $maxvotes) {
  templates::dynDialogError($i18n->msg("ballotsexactly", array($maxvotes)));
}

if (count($ballots) > $maxvotes) {
  templates::dynDialogError($i18n->msg("maxballots", array($maxvotes)));
}
?>
<h4 class="mdl-dialog__title"><?=$i18n->msg("confirmation")?></h4>
<div class="mdl-dialog__content">
  <p><?=$i18n->msg("confirmationExp")?> <?php foreach ($ballots as $ballot) { $hsl = hex2hsl($ballot["color"]); ?><code style="color: <?=($hsl[2] > 0.5 ? "rgba(0, 0, 0, .87)" : "rgb(255, 255, 255)")?>; background-color: #<?=$ballot["color"]?>; font-weight: bold;"><?=$ballot["name"]?></code> <?php } ?></p>
</div>
<div class="mdl-dialog__actions">
  <form method="POST" action="dovote.php"><input type="hidden" name="voting" value="<?=(int)$voting?>"><input type="hidden" name="ballot" value="<?=$_GET['id']?>"><input type="hidden" name="sent" value="1"><button type="submit" class="mdl-button md-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("yes")?><span class="mdl-ripple"></span></button></form>
  <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect"><?=$i18n->msg("no")?></button>
</div>

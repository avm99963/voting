<?php
require_once(__DIR__."/../core.php");

if (!citizen::loggedIn()) {
  die("Resource not found");
}

$i18n = new i18n("dashboard", 1);

if (!isset($_GET["id"])) {
  die("Resource not found");
}

$id = (int)$_GET["id"];

$query = mysqli_query($con, "SELECT * FROM voting_ballots WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("Resource not found");
}

$row = mysqli_fetch_assoc($query);

if ($votestatus = voting::canVote($row["voting"]) < 1) {
  die("Resource not found");
}
?>
<h4 class="mdl-dialog__title"><?=$row["name"]?></h4>
<div class="mdl-dialog__content" style="color: rgba(0, 0, 0, .87);">
  <?=$row["description"]?>
</div>
<div class="mdl-dialog__actions">
  <button onclick="dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect"><?=$i18n->msg("ok")?></button>
</div>

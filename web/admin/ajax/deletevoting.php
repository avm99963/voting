<?php
require_once(__DIR__."/../../core.php");

if (!user::loggedIn()) {
  die("You're not logged in.");
}

$i18n = new i18n("adminvotings", 2);

if (user::role() != 0) {
  die("You don't have permissions.");
}

$id = (int)$_REQUEST["id"];

if (empty($id)) {
  die("This voting does not exist");
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$row = mysqli_fetch_assoc($query);

if ($row["status"] != 0) {
  die("This voting is already published and therefore cannot be deleted.");
}
?>
<h4 class="mdl-dialog__title"><?=$i18n->msg("deletevoting")?></h4>
<div class="mdl-dialog__content">
  <p><?=$i18n->msg("delete_areyousure_object", array($row["name"]))?></p>
</div>
<div class="mdl-dialog__actions">
  <form method="POST" action="dodeletevoting.php"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="sent" value="1"><button type="submit" class="mdl-button md-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("yes")?><span class="mdl-ripple"></span></button></form>
  <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect"><?=$i18n->msg("no")?></button>
</div>

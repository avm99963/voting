<?php
require_once(__DIR__."/../../core.php");

if (!user::loggedIn()) {
  die("You're not logged in.");
}

$i18n = new i18n("adminusers", 2);

$query = mysqli_query($con, "SELECT * FROM users WHERE id = ".(int)$_REQUEST['id']) or die("<div class='alert-danger'>".mysqli_error()."</div>");
if (!mysqli_num_rows($query)) {
  die("This user doesn't exist.");
}
$row = mysqli_fetch_assoc($query);

if (!user::canEditUser($row["type"])) {
  die("Action not allowed.");
}
?>
<h4 class="mdl-dialog__title"><?=$i18n->msg("deleteuser")?></h4>
<div class="mdl-dialog__content">
  <p><?=$i18n->msg("delete_areyousure_object", array($row["name"]))?></p>
</div>
<div class="mdl-dialog__actions">
  <form method="POST" action="dodeleteuser.php"><input type="hidden" name="id" value="<?=(int)$_REQUEST['id']?>"><input type="hidden" name="sent" value="1"><button type="submit" class="mdl-button md-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("yes")?><span class="mdl-ripple"></span></button></form>
  <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect"><?=$i18n->msg("no")?></button>
</div>

<?php
require_once(__DIR__."/../../core.php");

if (!user::loggedIn()) {
  die("You're not logged in");
}

$i18n = new i18n("adminvotings", 2);

if (user::role() != 0) {
  die("You don't have permissions");
}

$id = (int)$_GET["id"];

if (empty($id)) {
  die("This ballot does not exist");
}

$query = mysqli_query($con, "SELECT * FROM voting_ballots WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This ballot does not exist");
  exit();
}

$row = mysqli_fetch_assoc($query);

if (voting::isPublished($row["voting"])) {
  templates::dynDialogError("This voting is already published and therefore cannot be modified");
}
?>
<form action="newballot.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title"><?=$i18n->msg("editballot")?></h4>
  <div class="mdl-dialog__content">
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
      <label class="mdl-textfield__label" for="color"><?=$i18n->msg("color")?></label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("edit")?></button>
    <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
  </div>
</form>

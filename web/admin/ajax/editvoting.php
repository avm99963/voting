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
  die("This voting does not exist.");
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$row = mysqli_fetch_assoc($query);
?>
<form action="doeditvoting.php" method="POST" autocomplete="OFF">
  <h4 class="mdl-dialog__title"><?=$i18n->msg("editvoting")?></h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="id" id="id" autocomplete="off" value="<?=$row["id"]?>" readonly>
      <label class="mdl-textfield__label" for="id"><?=$i18n->msg("id")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" value="<?=$row["name"]?>"<?=($row["status"] != 0 ? " readonly" : "")?>>
      <label class="mdl-textfield__label" for="name"><?=$i18n->msg("name")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <textarea class="mdl-textfield__input" name="description" rows="3" id="description" autocomplete="off"<?=($row["status"] != 0 ? " readonly" : "")?>><?=$row["description"]?></textarea>
      <label class="mdl-textfield__label" for="description"><?=$i18n->msg("description")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="datetime-local" name="datebegins" id="datebegins" autocomplete="off" value="<?=date("Y-m-d\TH:i:s", $row["datebegins"])?>"<?=($row["status"] != 0 ? " readonly" : "")?>>
      <label class="mdl-textfield__label always-focused" for="datebegins"><?=$i18n->msg("datebegins")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="datetime-local" name="dateends" id="dateends" autocomplete="off" value="<?=date("Y-m-d\TH:i:s", $row["dateends"])?>"<?=($row["status"] != 0 ? " readonly" : "")?>>
      <label class="mdl-textfield__label always-focused" for="dateends"><?=$i18n->msg("dateends")?></label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"<?=($row["status"] != 0 ? " disabled" : "")?>><?=$i18n->msg("edit")?></button>
    <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
  </div>
</form>

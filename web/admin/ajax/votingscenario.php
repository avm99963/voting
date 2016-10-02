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
<form action="dovotingscenario.php" method="POST" autocomplete="OFF">
  <input type="hidden" name="voting" value="<?=$id?>">
  <h4 class="mdl-dialog__title"><?=$i18n->msg("votingscenario")?></h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="maxvotingballots" id="maxvotingballots" autocomplete="off" value="<?=$row["maxvotingballots"]?>"<?=($row["status"] != 0 ? " readonly" : "")?>>
      <label class="mdl-textfield__label" for="maxvotingballots"><?=$i18n->msg("maxvotingballots")?></label>
    </div>
    <br>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="variable">
        <input type="checkbox" id="variable" name="variable" class="mdl-switch__input"<?=($row["variable"] == 1 ? " checked" : "")?><?=($row["status"] != 0 ? " disabled" : "")?>>
        <span class="mdl-switch__label"><?=$i18n->msg("variableopt")?></span>
      </label>
    </p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"<?=($row["status"] != 0 ? " disabled" : "")?>><?=$i18n->msg("edit")?></button>
    <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
  </div>
</form>

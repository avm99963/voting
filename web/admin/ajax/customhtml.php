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
  die("This voting does not exist");
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$row = mysqli_fetch_assoc($query);
?>
<form action="savecustomhtml.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title"><?=$i18n->msg("editcustomhtml")?></h4>
  <div class="mdl-dialog__content">
    <input type="hidden" name="voting" value="<?=$id?>">
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="enabled">
        <input type="checkbox" id="enabled" name="enabled" value="enabled" class="mdl-switch__input"<?=(!empty($row["customhtml"]) ? " checked" : "")?>>
        <span class="mdl-switch__label"><?=$i18n->msg("enablecustomhtml")?></span>
      </label>
    </p>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="customhtmlcontainer"<?=(empty($row["customhtml"]) ? " style='display:none;'" : "")?>>
      <textarea class="mdl-textfield__input" name="customhtml" rows="3" id="customhtml" autocomplete="off"><?=$row["customhtml"]?></textarea>
      <label class="mdl-textfield__label" for="customhtml"><?=$i18n->msg("customhtml")?></label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("edit")?></button>
    <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
  </div>
</form>
<dynscript>
document.querySelector("#enabled").addEventListener("change", function() {
  document.querySelector("#customhtmlcontainer").style.display = (this.checked == true ? "block" : "none");
});
</dynscript>

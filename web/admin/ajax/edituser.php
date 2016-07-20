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
<form action="doedituser.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title"><?=$i18n->msg("edituser")?></h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="id" id="id" value="<?=$row['id']?>" readonly="readonly" autocomplete="off">
      <label class="mdl-textfield__label" for="nombre"><?=$i18n->msg("id")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="username" id="username" value="<?=$row['username']?>" autocomplete="off">
      <label class="mdl-textfield__label" for="username"><?=$i18n->msg("username")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off">
      <label class="mdl-textfield__label" for="password"><?=$i18n->msg("password")?></label>
    </div>
    <br>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="type" id="type" class="mdlext-selectfield__select">
        <option value=""></option>
        <?php
        for ($i = user::minUserAddRole(); $i <= user::$maxrole; $i++) {
          $selected = ($i == $row["type"] ? " selected" : "");
          echo '<option value="'.$i.'"'.$selected.'>'.$i18n->msg("type_".$i).'</option>';
        }
        ?>
      </select>
      <label for="type" class="mdlext-selectfield__label"><?=$i18n->msg("type")?></label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="name" id="name"  value="<?=$row['name']?>" autocomplete="off">
      <label class="mdl-textfield__label" for="name"><?=$i18n->msg("realname")?></label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent"><?=$i18n->msg("edit")?></button>
    <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel"><?=$i18n->msg("cancel")?></button>
  </div>
</form>

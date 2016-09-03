<?php
require_once(__DIR__."/../../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("admincensus", 2);

if (!isset($_GET["id"])) {
  die("Resource not found");
}

$query = mysqli_query($con, "SELECT * FROM generatedcodes WHERE id = ".(int)$_GET["id"]);

if (!mysqli_num_rows($query)) {
  die("Resource not found");
}

$row = mysqli_fetch_assoc($query);
?>
<style>
.nostylelist {
  padding-left: 0!important;
}
.nostylelist li {
  list-style: none;
}
#qrcode {
  display: block;
  margin-left: auto;
  margin-right: auto;
}
</style>
<h4 class="mdl-dialog__title"><?=$row["name"]?></h4>
<div class="mdl-dialog__content">
  <ul class="nostylelist">
    <li><b><?=$i18n->msg("dni")?></b>: <?=$row["dni"]?></li>
    <li><b><?=$i18n->msg("birthday")?></b>: <?=date("d/m/Y", $row["birthday"])?></li>
    <li><b><?=$i18n->msg("code")?></b>: <code><?=$row["code"]?></code></li>
    <li><b><?=$i18n->msg("status")?></b>: <?=$row["status"]?></li>
    <li><b><?=$i18n->msg("creationmethod")?></b>: <?=$row["method"]?></li>
    <?php
    if ($row["method"] == 2) {
      ?>
      <li><b><?=$i18n->msg("creator")?></b>: <?=user::userData("name", $row["usercreation"])?></li>
      <?php
    }
    ?>
  </ul>
  <canvas id="qrcode"></canvas>
</div>
<div class="mdl-dialog__actions">
  <button onclick="dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect"><?=$i18n->msg("ok")?></button>
</div>
<dynscript>
qr.canvas({
  canvas: document.querySelector("#qrcode"),
  value: "<?=$row["code"]?>",
  size: 4,
  foreground: "rgba(0,0,0,.54)"
});
</dynscript>

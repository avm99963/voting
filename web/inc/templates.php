<?php
class templates {
  public static function dynDialogError($msg) {
    global $i18n;
    echo '<h4 class="mdl-dialog__title">'.$i18n->msg("error").'</h4>
<div class="mdl-dialog__content">
  <p>'.$msg.'</p>
</div>
<div class="mdl-dialog__actions">
  <button onclick="event.preventDefault(); dynDialog.close();" class="mdl-button mdl-js-button mdl-js-ripple-effect">'.$i18n->msg("ok").'</button>
</div>';
    exit();
  }
}

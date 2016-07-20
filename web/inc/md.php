<?php
class md {
  public static function snackbar($msg) {
    echo '<div class="mdl-snackbar mdl-js-snackbar">
      <div class="mdl-snackbar__text"></div>
      <button type="button" class="mdl-snackbar__action"></button>
    </div>
    <script>
    window.addEventListener("load", function() {
      var notification = document.querySelector(".mdl-js-snackbar");
      notification.MaterialSnackbar.showSnackbar(
        {
          message: "'.htmlspecialchars($msg).'"
        }
      );
    });
    </script>';
  }

  public static function msg($allowed) {
    global $_GET;
    global $i18n;

    if (!is_array($allowed)) {
      return false;
    }

    if (isset($_GET["msg"]) && in_array($_GET["msg"], $allowed)) {
      md::snackbar($i18n->msg("msg_".$_GET["msg"]));
    }

    return true;
  }

  public static function backBtn($url) {
    return '<a class="backbtn" href="'.$url.'"><i class="material-icons">arrow_back</i></a><div style="width:16px;"></div>';
  }
}

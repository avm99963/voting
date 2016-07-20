<?php
class i18n {
  public $hllist = array("af", "ar", "ca", "cs", "da", "de", "el", "en", "es", "fi", "fr", "he", "hu", "it", "ja", "ko", "nl", "no", "pl", "pt", "ro", "ru", "sr", "sv", "tr", "uk", "vi", "zh", "empty", "codes");
  public $i18n_strings = null;
  public $language = null;

  function __construct($include, $gobacki=0) {
    global $_GET;
    global $conf;

    if (empty($this->i18n_strings)) {
      $this->i18n_strings = array();
    }

    if (isset($_GET["hl"]) && in_array($_GET["hl"], $this->hllist)) {
      $this->language = $_GET["hl"];
    } else {
      $this->language = $conf["language"];
    }

    /* Messy code which allows me to be able to load empty strings :-D */
    if ($this->language == "empty" || $this->language == "codes") {
      return true;
    }
    /* End of messy code */

    $goback = str_repeat("../", (int)$gobacki);

    $this->i18n_strings = json_decode(file_get_contents($goback."locales/".$this->language."/global.json"), true);

    if (gettype($include) == "string") {
      $include = array($include);
    } elseif (gettype($include) != "array") {
      return false;
    }

    foreach ($include as $includer) {
      $file = $goback."locales/".$this->language."/".$includer.".json";
      if (@file_exists($file)) {
        $this->i18n_strings = array_merge($this->i18n_strings, json_decode(file_get_contents($file), true));
      } else {
        die("There are no translations for file ".$file." (language ".$this->language.").");
        return false;
      }
    }

    return true;
  }

  function msg($message, $strings = null) {
    if ($this->language == "codes") {
      return $message;
    }

    if (!isset($this->i18n_strings[$message])) {
      return false;
    }

    $string = $this->i18n_strings[$message];

    if ($strings != null && is_array($strings)) {
      foreach ($strings as $i => $subst) {
        $string = str_replace("%".$i, $subst, $string);
      }
    }

    return $string;
  }

  function initi18n_js($include, $gobacki=0) {
    $this->language = getlanguagei18n();

    $goback = str_repeat("../", (int)$gobacki);

    $file = $goback."locales/".$this->language."/".$include.".json";
    if (file_exists($file)) {
      echo "<script>var i18n = ".file_get_contents($file).";</script>";
    } else {
      die("<div class='alert alert-danger'>File ".$file." doesn't exist</div>");
      return false;
    }
  }
}

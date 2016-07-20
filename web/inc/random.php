<?php
require_once(__DIR__."/../lib/random_compat/random.php");

class random {
  public static function generateCode($length=32) {
    $letters = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"); // 62 possible characters
    $code = "";
    for ($i = 0; $i < $length; $i++) {
      $code .= $letters[random_int(0, 61)];
    }
    return $code;
  }
}

<?php
require_once(__DIR__."/../lib/random_compat/random.php");

class crypto {
  public static function shavote($dni, $ballot_name) {
    $salt = bin2hex(random_bytes(4));
    return array(
      "salt" => $salt,
      "hash" => hash("sha512", $dni.$ballot_name.$salt)
    );
  }
}

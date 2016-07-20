<?php
class sanitizer {
  public static function dbString($string) {
    global $con;
    return mysqli_real_escape_string($con, htmlspecialchars($string));
  }
}

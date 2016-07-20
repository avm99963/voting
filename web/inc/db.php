<?php
class db {
  public static function table($sql) {
    global $con;

    $query = mysqli_query($con, $sql);

    if (!$query) {
      return false;
    }

    $return = array();

    while ($row = mysqli_fetch_assoc($query)) {
      $return[] = $row;
    }

    return $return;
  }
}

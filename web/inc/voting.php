<?php
class voting {
  public static function votingData($data, $id) {
    global $con;

    $query = mysqli_query($con, "SELECT $data FROM votings WHERE id = ".(int)$id);

    if (!mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);

    return $row[$data];
  }

  public static function isPublished($id) {
    $status = voting::votingData("status", $id);

    return ($status == 1);
  }
}

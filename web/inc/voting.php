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

  private static function getList($file) {
    $file = file::read($file);

    return explode("\n", $file);
  }

  public static function canVote($votingid, $userid="currentuser") {
    global $_SESSION;
    global $con;

    $query = mysqli_query($con, "SELECT datebegins, dateends, status, filters FROM votings WHERE id = ".(int)$votingid." LIMIT 1") or die(mysqli_error($con));

    if (!mysqli_num_rows($query)) {
      return 0;
    }

    $row = mysqli_fetch_assoc($query);

    $time = time();

    if ($time < $row["datebegins"]) {
      return 0;
    } elseif ($time < $row["dateends"]) {
      if (!isset($row["filters"]) || $row["filters"] == "") {
        return true;
      } else {
        $filters = json_decode($row["filters"], true);
        if ($filters["whitelist"]["enabled"] == 1) {
          // Check if user is in whitelist
          $list = voting::getList($filters["whitelist"]["file"]);

          if (!in_array(citizen::userData("dni"), $list)) {
            return -1;
          }
        }
        if ($filters["blacklist"]["enabled"] == 1) {
          // Check if user is in blacklist
          $list = voting::getList($filters["blacklist"]["file"]);

          if (in_array(citizen::userData("dni"), $list)) {
            return -2;
          }
        }
        if ($filters["age"]["enabled"] == 1) {
          // Check user age

          // Code extracted from http://stackoverflow.com/questions/3776682/php-calculate-age
          $birthDate = explode("/", date("m/d/Y", citizen::userData("birthday")));
          $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
            ? ((date("Y") - $birthDate[2]) - 1)
            : (date("Y") - $birthDate[2]));

          if ($age < $filters["age"]["age"]) {
            return -3;
          }
        }
      }
      return 1;
    } else {
      return 0;
    }

    return 0;
  }
}

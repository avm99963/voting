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

    if ($row["status"] != 1) {
      return 0;
    }

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

          $birthday = citizen::userData("birthday");

          if ($birthday === false) {
            return -3;
          }

          // Code extracted from http://stackoverflow.com/questions/3776682/php-calculate-age
          $birthDate = explode("/", date("m/d/Y", $birthday));
          $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
            ? ((date("Y") - $birthDate[2]) - 1)
            : (date("Y") - $birthDate[2]));

          if ($age < $filters["age"]["age"]) {
            return -3;
          }
        }
      }

      $query2 = mysqli_query($con, "SELECT id FROM voted WHERE voting = ".(int)$votingid." AND dni = '".citizen::userData("dni")."'");

      if (mysqli_num_rows($query2) > 0) {
        return -4;
      }

      if (citizen::method() == citizen::CODE && citizen::usesLeft() < 1) {
        return -5;
      }

      return 1;
    } else {
      return 0;
    }

    return 0;
  }

  public static function doVote($voting, $votes) {
    global $_SESSION;
    global $con;

    if (voting::canVote($voting) < 1) {
      return array("error" => 0);
    }

    if (count($votes) !== count(array_unique($votes))) {
      return array("error" => 0);
    }

    $maxvotes = voting::votingData("maxvotingballots", $voting);
    $variable = voting::votingData("variable", $voting);

    if (count($votes) > $maxvotes) {
      return array("error" => 0);
    }

    if ($variable == 1 && count($votes) != $maxvotes) {
      return array("error" => 0);
    }

    $name = sanitizer::dbString(citizen::userData("name"));
    $dni = sanitizer::dbString(citizen::userData("dni"));

    $ballots = array();

    foreach ($votes as $vote) {
      $query = mysqli_query($con, "SELECT id, name, voting FROM voting_ballots WHERE id = ".(int)$vote);

      if (!mysqli_num_rows($query)) {
        return array("error" => 0);
      }

      $row = mysqli_fetch_assoc($query);

      if ($row["voting"] != $voting) {
        return array("error" => 0);
      }

      do {
        $row["shahash"] = crypto::shavote($dni, $row["name"]);

        $query2 = mysqli_query($con, "SELECT id FROM ballots WHERE shahash = '".$row["shahash"]["hash"]."'");
      } while (mysqli_num_rows($query2));

      $ballots[] = $row;
    }

    if (citizen::method() == citizen::CODE) {
      $method = 0; // code
      $generatedcode = $_SESSION["citizenid"];
      $birthday = sanitizer::dbString(citizen::userData("birthday"));

      // We increment the uses count for the generated code.
      if (!mysqli_query($con, "UPDATE generatedcodes SET usesdone = usesdone + 1 WHERE id = ".$_SESSION["citizenid"]." LIMIT 1")) {
        return array("error" => 1);
      }
    } else {
      return array("error" => 0);
    }

    do {
      $id = random_int(1, 999999999);
      $idquery = mysqli_query($con, "SELECT id FROM voted WHERE id = ".$id);
    } while (mysqli_num_rows($idquery));

    // Submitting into people who voted table
    if (!mysqli_query($con, "INSERT INTO voted (id, method, generatedcode, name, dni, birthday, voting) VALUES ($id, $method, $generatedcode, '$name', '$dni', $birthday, $voting)")) {
      die(mysqli_error($con));
      return array("error" => 2);
    }

    $return = array("done" => "ok", "ballots" => array());

    // Submitting ballots
    foreach ($ballots as $ballot) {
      if (!mysqli_query($con, "INSERT INTO ballots (ballot, voting, shahash) VALUES (".$ballot["id"].", ".(int)$voting.", '".mysqli_real_escape_string($con, $ballot["shahash"]["hash"])."')")) {
        return array("error" => 3);
      }
      $return["ballots"][] = array("ballot" => $ballot["id"], "name" => $ballot["name"], "salt" => $ballot["shahash"]["salt"]);
    }

    return $return;
  }

  public static function generateResults($voting) {
    global $con;

    $query = mysqli_query($con, "SELECT id FROM voting_defaultresults WHERE voting = ".(int)$voting);

    if (mysqli_num_rows($query)) {
      return true;
    } else {
      $query2 = mysqli_query($con, "SELECT ballot, COUNT(*) FROM ballots WHERE voting = ".(int)$voting." GROUP BY ballot");

      $votes = array();

      while ($row2 = mysqli_fetch_row($query2)) {
        $votes[$row2[0]] = $row2[1];
      }

      $query3 = mysqli_query($con, "SELECT id, name FROM voting_ballots WHERE voting = ".(int)$voting);

      $allballots = array();

      if (mysqli_num_rows($query3)) {
        while ($row3 = mysqli_fetch_assoc($query3)) {
          if (!isset($votes[$row3["id"]])) {
            $votes[$row3["id"]] = 0;
          }

          $allballots[$row3["id"]] = $row3["name"];
        }
      }

      $count = json_encode($votes);

      $query4 = mysqli_query($con, "SELECT ballot, shahash FROM ballots WHERE voting = ".(int)$voting);

      $submittedballots = array();

      while ($row4 = mysqli_fetch_assoc($query4)) {
        $submittedballots[] = array("ballot" => $row4["ballot"], "shahash" => $row4["shahash"]);
      }

      $jsonfile = json_encode(array("available_ballots" => $allballots, "submitted_ballots" => $submittedballots));

      if (!file::canUpload()) {
        die("Please, assign upload permissions to PHP for the uploads folder.");
      }

      $filename = file::save("ballot.json", $jsonfile);

      if (mysqli_query($con, "INSERT INTO voting_defaultresults (voting, generated, results, ballotsfile) VALUES (".(int)$voting.", 1, '".mysqli_real_escape_string($con, $count)."', '".mysqli_real_escape_string($con, $filename)."')")) {
        return true;
      } else {
        return false;
      }
    }
  }

  public static function defaultResults($voting) {
    global $con;

    $query = mysqli_query($con, "SELECT * FROM voting_defaultresults WHERE voting = ".(int)$voting." LIMIT 1");

    if (!mysqli_num_rows($query)) {
      return false;
    }

    return mysqli_fetch_assoc($query);
  }
}

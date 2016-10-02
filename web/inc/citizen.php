<?php
class citizen {
  public static function codeLogin($code) {
    global $con;
    global $_SESSION;
    $query = mysqli_query($con, "SELECT id FROM generatedcodes WHERE code='".$code."'");
    if (mysqli_num_rows($query)) {
      $row = mysqli_fetch_assoc($query) or die(mysqli_error($con));
      $_SESSION["citizenid"] = $row["id"];
      $_SESSION["citizenloginmethod"] = "CODE";
      return 1;
    } else {
      return 0;
    }
  }

  public static function userData($data2, $userid="currentuser") {
    global $_SESSION;
    global $con;

    if ($userid == 'currentuser' && $_SESSION["citizenloginmethod"] == "DNIE") {
      return false;
    } else {
      if ($userid == 'currentuser') {
    		$id = $_SESSION['citizenid'];
    	} else {
    		$id = $userid;
    	}

      $data = mysqli_real_escape_string($con, $data2);
      $query = mysqli_query($con, "SELECT ".$data." FROM generatedcodes WHERE id = '".$id."'");

      if ($query === false) {
        return false;
      }

      if (!mysqli_num_rows($query)) {
        return false;
      }

    	$row = mysqli_fetch_assoc($query);

    	return $row[$data];
    }
  }

  public static function loggedIn() {
    if (isset($_SESSION["citizenloginmethod"])) {
      return true;
    } else {
      return false;
    }
  }
}

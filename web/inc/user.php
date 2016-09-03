<?php
class user {
  private static $role = null;

  public static function login($usr, $pwd) {
    global $con;
    global $_SESSION;
    $query = mysqli_query($con, "SELECT * FROM users WHERE username='".$usr."'");
    if (mysqli_num_rows($query)) {
      $row = mysqli_fetch_assoc($query) or die(mysqli_error($con));
      if (password_verify($_POST["password"], $row["password"])) {
        $_SESSION['id'] = $row['id'];
        return 2;
        // @TODO: Return 1 in case of 2-step verification
      } else {
        return 0;
      }
    } else {
      return 0;
    }
  }

  public static function userData($data2, $userid="currentuser") {
    global $_SESSION;
    global $con;
    if ($userid == 'currentuser') {
  		$id = $_SESSION['id'];
  	} else {
  		$id = $userid;
  	}

    $data = mysqli_real_escape_string($con, $data2);
    $query = mysqli_query($con, "SELECT ".$data." FROM users WHERE id = '".$id."'");

    if ($query === false) {
      return false;
    }

    if (!mysqli_num_rows($query)) {
      return false;
    }

  	$row = mysqli_fetch_assoc($query);

  	return $row[$data];
  }

  public static function role($userid="currentuser") {
    if (user::$role === null) {
      user::$role = user::userData("type", $userid);
    }
    return user::$role;
  }

  public static function loggedIn() {
    if (isset($_SESSION["id"])) {
      return true;
    } else {
      return false;
    }
  }

  public static function minUserAddRole() {
    $role = user::role();
    return ($role == 0 ? 0 : ($role + 1));
  }

  public static function canEditUser($userrole) {
    $role = user::role();

    if ($role == 0) {
      return true;
    }

    if ($userrole <= $role) {
      return false;
    } else {
      return true;
    }
  }

  public static $maxrole = 2;
}

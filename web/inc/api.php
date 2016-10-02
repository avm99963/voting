<?php
class api {
  private $apikeyid = -1;

  public function init($apikey) {
    global $con;

    if ($this->apikeyid != -1) {
      return true;
    }

    if (empty($apikey)) {
      return false;
    }

    if (strlen($apikey) != 32) {
      return false;
    }

    $query = mysqli_query($con, "SELECT id, voting FROM apikeys WHERE keytext = '".mysqli_real_escape_string($con, $apikey)."' AND status = 0");

    if (!mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);

    $query2 = mysqli_query($con, "SELECT id FROM votings WHERE id = ".$row["voting"]." AND status = 1");

    if (!mysqli_num_rows($query2)) {
      return false;
    }

    $this->apikeyid = $row["id"];

    return true;
  }

  public function init_action() {
    global $con;

    if ($this->apikeyid == -1) {
      return false;
    }

    $query = mysqli_query($con, "SELECT name FROM votings INNER JOIN apikeys ON votings.id = apikeys.voting WHERE apikeys.id = ".$this->apikeyid." LIMIT 1");

    if (mysqli_num_rows($query)) {
      $row = mysqli_fetch_assoc($query);

      $return = array();

      $return["status"] = "ok";
      $return["payload"] = array();
      $return["payload"]["votingName"] = $row["name"];

      return $return;
    } else {
      return $this->error(4, "Unexpected error: did not find the voting.");
    }
  }

  public function error($code, $msg) {
    $return = array();

    $return["status"] = "error";
    $return["errorcode"] = (int)$code;
    $return["errormsg"] = $msg;

    return $return;
  }
}

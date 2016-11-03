<?php
class api {
  private $apikeyid = -1;
  private $uses = 0;

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

    $query = mysqli_query($con, "SELECT id, voting, uses FROM apikeys WHERE keytext = '".mysqli_real_escape_string($con, $apikey)."' AND status = 0");

    if (!mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);

    $query2 = mysqli_query($con, "SELECT id FROM votings WHERE id = ".$row["voting"]." AND status = 1");

    if (!mysqli_num_rows($query2)) {
      return false;
    }

    $this->apikeyid = $row["id"];
    $this->uses = $row["uses"];

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

  public function add_census($name, $dni) {
    global $con;

    $birthday = 0;
    $name = sanitizer::dbString($name);
    $dni = sanitizer::dbString($dni);
    $birthday = 0;

    if (preg_match("/(\d{8})([a-zA-Z]{1})/", $dni) === false) {
      $this->error(1, "Incorrect DNI");
    }

    do {
      $code = mysqli_real_escape_string($con, random::generateCode(16));
      $query = mysqli_query($con, "SELECT id FROM generatedcodes WHERE code = '".$code."'");
    } while (mysqli_num_rows($query));

    $sql6 = "INSERT INTO generatedcodes (code, name, dni, birthday, method, status, creation, uses, usesdone) VALUES ('$code', '$name', '$dni', $birthday, 1, 0, ".time().", ".$this->uses.", 0)";
    if (mysqli_query($con,$sql6)) {
      $return = array();

      $return["status"] = "ok";
      $return["payload"] = array();
      $return["payload"]["code"] = $code;

      return $return;
    } else {
      $this->error(1, "An unexpected error occurred while generating the code.");
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

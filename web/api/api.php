<?php
/**
  *  /|||||\  |||||\  ||||||
  *  ||   ||  ||   ||   ||
  *  |||||||  |||||/    ||
  *  ||   ||  ||        ||
  *  ||   ||  ||      |||||| .php
  */

require_once("../core.php");

header("Access-Control-Allow-Origin: *");

$api = new api();

$return = array();

if (isset($_REQUEST["apikey"]) && $api->init($_REQUEST["apikey"])) {
  if (isset($_REQUEST["action"])) {
    switch ($_REQUEST["action"]) {
      case "init":
      $return = $api->init_action();
      break;

      case "addcensus":
      $name = $_POST["name"];
      $dni = $_POST["dni"];
      $return = $api->add_census($name, $dni);
      break;

      default:
      $return = $api->error(3, "Action '".$_REQUEST["action"]."' not recognised");
    }
  } else {
    $return = $api->error(2, "Action not defined");
  }
} else {
  $return = $api->error(1, "API key not valid");
}

echo json_encode($return);

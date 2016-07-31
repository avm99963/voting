<?php
require_once(__DIR__."/../core.php");

set_time_limit(30); // disable the time limit for this script

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

/**
  * Set variables $file and $download_filename.
  *
  * $file: name of the file stored in the "uploads" folder
  * $download_filename: name of the file which will appear when downloading
  */
if (isset($_GET["well"])) {
  switch($_GET["well"]) {
    case "whitelist":
    case "blacklist":

    if (user::role() != 0) {
      die("You don't have permissions.");
    }

    $id = (int)$_GET["id"];

    if (empty($id)) {
      die("Empty id");
    }

    $query = mysqli_query($con, "SELECT filters FROM votings WHERE id = ".$id);

    if (!mysqli_num_rows($query)) {
      die("This voting does not exist");
      exit();
    }

    $row = mysqli_fetch_assoc($query);

    $filters = json_decode($row["filters"], true);

    $file = $filters[$_GET["well"]]["file"];
    $download_filename = $_GET["well"]."_".$id.".txt";

    break;

    default:
    die("Well not defined.");
  }

  $path = __DIR__."/../uploads/"; // change the path to fit your websites document structure
  $dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})", '', $file); // simple file name validation
  $fullPath = $path.$dl_file;

  if ($fd = fopen($fullPath, "r")) {
      $fsize = filesize($fullPath);
      $path_parts = pathinfo($fullPath);
      $ext = strtolower($path_parts["extension"]);
      header("Content-length: $fsize");
      if (isset($_GET["view"])) {
        header("Content-type: text/plain");
      } else {
        header("Content-type: application/octet-stream");
        header("Content-Disposition: filename=\"".$download_filename."\"");
        header("Cache-control: private"); //use this to open files directly
      }
      while(!feof($fd)) {
          $buffer = fread($fd, 2048);
          echo $buffer;
      }
  }
  fclose ($fd);
  exit;
}

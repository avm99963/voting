<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

if (user::role() != 0) {
  die("You don't have permissions.");
}

$id = (int)$_POST["voting"];

if (empty($id)) {
  header("Location: votings.php");
  exit();
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
  exit();
}

$row = mysqli_fetch_assoc($query);

if ($row["status"] != 0) {
  die("This voting is already published and therefore cannot be edited.");
}

$current_filters = json_decode($row["filters"], true);

if (isset($_POST["whitelist_enabled"]) && isset($_POST["blacklist_enabled"])) {
  die("You cannot enable both the whitelist and the blacklist at the same time.");
}

$filters = array();
$filters["whitelist"] = array();
$filters["blacklist"] = array();
$filters["age"] = array();

if (isset($_POST["whitelist_enabled"])) {
  if ($_FILES["whitelist_file"]["error"] == UPLOAD_ERR_NO_FILE) {
    if (!count($current_filters) || (count($current_filters) && $current_filters["whitelist"]["enabled"] == 0)) {
      header("Location: filtervoting.php?id=".$id."&msg=empty");
      exit();
    } else {
      $filters["whitelist"] = $current_filters["whitelist"];
    }
  } else {
    if ($_FILES["whitelist_file"]["error"] != UPLOAD_ERR_OK) {
      die("An error occurred while uploading the whitelist.");
    }

    $name = ((count($current_filters) && $current_filters["whitelist"]["enabled"] == 1) ? $current_filters["whitelist"]["file"] : file::generateRandomName(".txt"));

    if (!file::canUpload()) {
      die("Please, assign upload permissions to PHP for the uploads folder.");
    }

    move_uploaded_file($_FILES["whitelist_file"]["tmp_name"], __DIR__."/../uploads/".$name);

    $filters["whitelist"]["enabled"] = 1;
    $filters["whitelist"]["file"] = $name;
  }
} else {
  $filters["whitelist"]["enabled"] = 0;
}

if (isset($_POST["blacklist_enabled"])) {
  if ($_FILES["blacklist_file"]["error"] == UPLOAD_ERR_NO_FILE) {
    if (!count($current_filters) || (count($current_filters) && $current_filters["blacklist"]["enabled"] == 0)) {
      header("Location: filtervoting.php?id=".$id."&msg=empty");
      exit();
    } else {
      $filters["blacklist"] = $current_filters["blacklist"];
    }
  } else {
    if ($_FILES["blacklist_file"]["error"] != UPLOAD_ERR_OK) {
      die("An error occurred while uploading the blacklist.");
    }

    $name = ((count($current_filters) && $current_filters["blacklist"]["enabled"] == 1) ? $current_filters["blacklist"]["file"] : file::generateRandomName(".txt"));

    if (!file::canUpload()) {
      die("Please, assign upload permissions to PHP for the uploads folder.");
    }

    move_uploaded_file($_FILES["blacklist_file"]["tmp_name"], __DIR__."/../uploads/".$name);

    $filters["blacklist"]["enabled"] = 1;
    $filters["blacklist"]["file"] = $name;
  }
} else {
  $filters["blacklist"]["enabled"] = 0;
}

if (isset($_POST["age_enabled"])) {
  $filters["age"]["enabled"] = 1;
  $filters["age"]["age"] = (int)$_POST["age"];
} else {
  $filters["age"]["enabled"] = 0;
}

$filters_json = mysqli_real_escape_string($con, json_encode($filters));

if (mysqli_query($con, "UPDATE votings SET filters = '$filters_json' WHERE id = $id LIMIT 1")) {
  header("Location: filtervoting.php?id=$id&msg=filteredit");
} else {
  die ("<p class='alert-danger'>Error saving the filters: " . mysqli_error($con) . "</p>");
}

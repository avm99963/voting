<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  die("You're not logged in");
}

$i18n = new i18n("adminvotings", 1);

if (user::role() != 0) {
  die("You don't have permissions");
}

$id = (int)$_POST["voting"];

if (empty($id)) {
  die("This voting does not exist");
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$html = mysqli_real_escape_string($con, ($_POST["enabled"] ? $_POST["customhtml"] : ""));

$sql = "UPDATE votings SET customhtml='$html' WHERE id=$id";
if (mysqli_query($con, $sql)) {
	header("Location: voting.php?id=$id&msg=customhtmlsuccess");
} else {
	die("[err00] Error deleting user: ".mysqli_error($con));
}

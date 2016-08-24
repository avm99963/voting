<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

if (user::role() != 0) {
  header("Location: votings.php");
  exit();
}

$id = (int)$_POST["id"];

if (empty($id)) {
  header("Location: votings.php");
  exit();
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$row = mysqli_fetch_assoc($query);

if ($row["status"] != 0) {
  die("This voting is already published and therefore cannot be deleted.");
}

$delete = array();
$delete["votings"] = "DELETE FROM votings WHERE id = $id LIMIT 1";
$delete["apikeys"] = "DELETE FROM apikeys WHERE voting = $id";
$delete["voting_ballots"] = "DELETE FROM voting_ballots WHERE voting = $id LIMIT 1";

foreach ($delete as $table => $sql) {
  if (mysqli_query($con, $sql)) {
  	header("Location: votings.php?msg=votingdelete");
  } else {
  	die ("[err00] Error deleting voting data from table '".$table."': ".mysqli_error($con));
  }
}

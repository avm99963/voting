<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminvotings", 1);

if (user::role() != 0) {
  header("Location: index.php");
  exit();
}

$id = (int)$_REQUEST["id"];

if (empty($id)) {
  header("Location: votings.php");
  exit();
}

$query = mysqli_query($con, "SELECT * FROM voting_ballots WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This ballot does not exist");
}

$row = mysqli_fetch_assoc($query);

if (voting::isPublished($row["voting"])) {
  die("This voting is already published and therefore cannot be modified");
}

$sql = "DELETE FROM voting_ballots WHERE id = '".(int)$_POST['id']."' LIMIT 1";
if (mysqli_query($con, $sql)) {
	header("Location: voting.php?id=".$row["voting"]."&msg=ballotdelete");
	} else {
	die ("[err00] Error deleting ballot: ".mysqli_error($con));
}

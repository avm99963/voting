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

$id = (int)$_REQUEST["id"];

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
  die("This voting is already published and therefore cannot be edited.");
}

$name = sanitizer::dbString($_POST['name']);
$description = sanitizer::dbString($_POST['description']);
$votingdescription = sanitizer::dbString($_POST['votingdescription']);
$datebegins = strtotime($_POST['datebegins']);
$dateends = strtotime($_POST['dateends']);

if (empty($name) || empty($datebegins) || empty($dateends)) {
  header("Location: voting.php?id=".$id."&msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

if ($datebegins > $dateends) {
  header("Location: voting.php?id=".$id."&msg=datediff");
  die("[ERR_03] The beginning date must be before the end date.");
}

$sql6 = "UPDATE votings SET name='".$name."', description='".$description."', votingdescription='".$votingdescription."', datebegins=".$datebegins.", dateends=$dateends WHERE id = ".$id." LIMIT 1";

if (mysqli_query($con, $sql6)) {
  header("Location: voting.php?id=".$id."&msg=votingnew");
} else {
  die ("<p class='alert-danger'>Error editing the user: " . mysqli_error($con) . "</p>");
}

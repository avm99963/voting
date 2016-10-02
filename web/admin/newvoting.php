<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

if (user::role() != 0) {
  header("Location: index.php");
  exit();
}

$name = sanitizer::dbString($_POST['name']);
$description = sanitizer::dbString($_POST['description']);
$votingdescription = sanitizer::dbString($_POST['votingdescription']);
$datebegins = strtotime($_POST['datebegins']);
$dateends = strtotime($_POST['dateends']);

if (empty($name) || empty($datebegins) || empty($dateends)) {
  header("Location: votings.php?msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

if ($datebegins > $dateends) {
  header("Location: votings.php?msg=datediff");
  die("[ERR_03] The beginning date must be before the end date.");
}

$sql6 = "INSERT INTO votings (name, description, votingdescription, datebegins, dateends, status, maxvotingballots, variable) VALUES ('$name', '$description', '$votingdescription', $datebegins, $dateends, 0, 1, 0)";
if (mysqli_query($con,$sql6)) {
  header("Location: votings.php?msg=votingadded");
  exit();
} else {
  die ("[ERR_01]: Error creating user: " . mysqli_error($con) . "</p>");
}

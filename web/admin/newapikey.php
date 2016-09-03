<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

if (user::role() > 1) {
  header("Location: index.php");
  exit();
}

$votingid = (int)$_POST["voting"];

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$votingid);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$row = mysqli_fetch_assoc($query);

$description = mysqli_real_escape_string($con, htmlspecialchars($_POST['description']));
$uses = (int)$_POST["uses"];

if ($uses < 1) {
  header("Location: apikeys.php");
}

$apikey = mysqli_real_escape_string($con, random::generateCode(32)); // We generate a cryptographically secure 32-character long API key
$query2 = mysqli_query($con, "SELECT * FROM votings WHERE keytext = '$apikey'");

while (mysqli_num_rows($query2)) {
  $apikey = mysqli_real_escape_string($con, random::generateCode(32));
  $query2 = mysqli_query($con, "SELECT * FROM votings WHERE keytext = '$apikey'");
}

$sql6 = "INSERT INTO apikeys (keytext, description, voting, userid, status, uses) VALUES ('$apikey', '$description', $votingid, ".$_SESSION["id"].", 0, ".$uses.")";
if (mysqli_query($con,$sql6)) { // ADDING
  header("Location: apikeys.php?id=".$votingid."&keyid=".mysqli_insert_id($con));
  exit();
} else {
  die ("[ERR_01]: Error generating api key: " . mysqli_error($con) . "</p>");
}

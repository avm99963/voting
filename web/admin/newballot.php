<?php
require_once(__DIR__."/../core.php");
require_once(__DIR__."/../lib/htmlpurifier/HTMLPurifier.standalone.php");

$purifier = new HTMLPurifier();

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

if (user::role() != 0) {
  header("Location: index.php");
  exit();
}

if (isset($_POST["ballot"])) { // EDITING
  $ballotid = (int)$_POST["ballot"];

  $query = mysqli_query($con, "SELECT * FROM voting_ballots WHERE id = ".$ballotid);

  if (!mysqli_num_rows($query)) {
    die("The ballot you're trying to edit does not exist");
  }

  $row = mysqli_fetch_assoc($query);
} else { // ADDING
  if (!isset($_POST["voting"]) || empty($_POST["voting"])) {
    die("This voting does not exist.");
  }

  $votingid = (int)$_POST["voting"];

  $query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$votingid);

  if (!mysqli_num_rows($query)) {
    die("This voting does not exist");
  }

  $row = mysqli_fetch_assoc($query);

  if ($row["status"] != 0) {
    die("This voting is already published and therefore cannot be edited.");
  }
}

$name = sanitizer::dbString(trim($_POST['name']));
$description = mysqli_real_escape_string($con, $purifier->purify($_POST['description']));
$color = sanitizer::dbString(str_replace("#", "", $_POST['color']));

if (empty($name)) {
  header("Location: voting.php?id=".$votingid."&msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

if (empty($color) || !ctype_xdigit($color) || strlen($color) != 6) {
  $color = "333333";
}

if (isset($_POST["ballot"])) { // EDITING
  $sql6 = "UPDATE voting_ballots SET name='$name', description='$description', color='$color' WHERE id=".$ballotid;
  if (mysqli_query($con,$sql6)) {
    header("Location: voting.php?id=".$row["voting"]."&msg=ballotnew");
    exit();
  } else {
    die ("[ERR_01]: Error creating user: " . mysqli_error($con) . "</p>");
  }
} else {
  $sql6 = "INSERT INTO voting_ballots (name, description, color, voting) VALUES ('$name', '$description', '$color', $votingid)";
  if (mysqli_query($con,$sql6)) { // ADDING
    header("Location: voting.php?id=".$votingid."&msg=ballotadded");
    exit();
  } else {
    die ("[ERR_01]: Error creating ballot: " . mysqli_error($con) . "</p>");
  }
}

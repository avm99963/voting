<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  die("You are not logged in");
}

$i18n = new i18n("adminvotings", 1);

if (user::role() > 1) {
  die("You don't have permissions");
}

$id = (int)$_POST["id"];

if (empty($id)) {
  die("This voting does not exist");
}

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id);

if (!mysqli_num_rows($query)) {
  die("This voting does not exist");
}

$row = mysqli_fetch_assoc($query);

if (mysqli_query($con, "UPDATE votings SET status = 1 WHERE id = ".$id." LIMIT 1")) {
  header("Location: voting.php?id=".$row["id"]."&msg=votingpublished");
  exit();
} else {
  die ("<p class='alert-danger'>Error deleting the voting: " . mysqli_error($con) . "</p>");
}

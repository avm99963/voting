<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$username = sanitizer::dbString($_POST['username']);
$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
$realname = sanitizer::dbString($_POST['realname']);
$type = (int)$_POST['type'];

if (empty($username) || empty($_POST["password"]) || empty($realname) || $type < 0 || $type > user::$maxrole) {
  header("Location: users.php?msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

if ($type < user::minUserAddRole() || $type > user::$maxrole) {
  die("[ERR_00] Action not allowed.");
}

if (mysqli_num_rows(mysqli_query($con, "SELECT * FROM users WHERE username = '".$username."'"))) {
  header("Location: users.php?msg=usernametaken");
  exit();
}

$sql6 = "INSERT INTO users (username, password, name, type) VALUES ('$username', '$password', '$realname', $type)";
if (mysqli_query($con,$sql6)) {
  header("Location: users.php?msg=addedsuccessful");
  exit();
} else {
  die ("[ERR_01]: Error creating user: " . mysqli_error($con) . "</p>");
}

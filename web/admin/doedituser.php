<?php

require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminusers", 1);

$query = mysqli_query($con, "SELECT * FROM users WHERE id = ".(int)$_REQUEST['id']) or die("<div class='alert-danger'>".mysqli_error()."</div>");
if (!mysqli_num_rows($query)) {
  die("This user doesn't exist.");
}
$row = mysqli_fetch_assoc($query);

if (!user::canEditUser($row["type"])) {
  die("Action not allowed.");
}

$id = (int)$_POST["id"];
$username = sanitizer::dbString($_POST['username']);

if (isset($_POST["password"]) && !empty($_POST["password"])) {
  $sqladded = ", password='".password_hash($_POST["password"], PASSWORD_DEFAULT)."'";
} else {
  $sqladded = "";
}

$realname = sanitizer::dbString($_POST['name']);
$type = (int)$_POST['type'];

if (empty($username) || empty($realname)) {
  header("Location: users.php?msg=empty");
  die("[ERR_02] Some parameters are missing.");
}

if ($type < user::minUserAddRole() || $type > user::$maxrole) {
  die("[ERR_00] Action not allowed.");
}

if (mysqli_num_rows(mysqli_query($con, "SELECT * FROM users WHERE username = '".$username."' AND id != ".$id))) {
  header("Location: user.php?id=".$id."&msg=usernametaken");
  exit();
}

$sql6 = "UPDATE users SET username='".$username."', name='".$realname."', type=".$type.$sqladded." WHERE id = ".$id." LIMIT 1";

if (mysqli_query($con, $sql6)) {
  header("Location: users.php?msg=usernew");
} else {
  die ("<p class='alert-danger'>Error editing the user: " . mysqli_error($con) . "</p>");
}

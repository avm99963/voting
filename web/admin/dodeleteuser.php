<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminusers", 1);

if (!isset($_POST['id']) && empty((int)$_POST['id'])) {
  die("This user doesn't exist.");
}

$query = mysqli_query($con, "SELECT * FROM users WHERE id = ".(int)$_REQUEST['id']) or die("<div class='alert-danger'>".mysqli_error()."</div>");
if (!mysqli_num_rows($query)) {
  die("This user doesn't exist.");
}
$row = mysqli_fetch_assoc($query);

if (!user::canEditUser($row["type"])) {
  die("Action not allowed.");
}

if ($row["id"] == $_SESSION["id"]) {
  header("Location: users.php");
  exit();
}

$sql = "DELETE FROM users WHERE id = '".(INT)$_POST['id']."' LIMIT 1";
if (mysqli_query($con, $sql)) {
	header("Location: users.php?msg=userdelete");
	} else {
	die ("[err00] Error deleting user: ".mysqli_error($con));
}

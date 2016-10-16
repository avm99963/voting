<?php
require_once(__DIR__."/../core.php");

if (!user::loggedIn()) {
  header('Location: login.php');
  exit();
}

$i18n = new i18n("adminresults", 1);

if (user::role() != 0) {
  header("Location: results.php");
  exit();
}

if (!isset($_GET["id"])) {
  header("Location: results.php");
  exit();
}

$id = (int)$_GET["id"];

$query = mysqli_query($con, "SELECT * FROM votings WHERE id = ".$id." AND status = 1 AND votings.dateends < ".(int)time());

if (!mysqli_num_rows($query)) {
  header("Location: results.php");
  exit();
}

$generated = voting::generateResults($id);

if ($generated === true) {
  header("Location: results.php?msg=resultgenerated");
  exit();
} else {
  header("Location: results.php?msg=resultnotgenerated");
  exit();
}

<?php
require_once(__DIR__."/../core.php");

$username = mysqli_real_escape_string($con, $_POST['username']);
if (empty($username) || empty($_POST["password"])) {
	header("Location: login.php?msg=empty");
	echo "Please fill in all form.";
} else {
	$status = user::login($username, $_POST["password"]);
	if ($status == 0) {
		header("Location: login.php?msg=loginwrong");
	} elseif ($status == 2) {
		// TODO: Implement 2-step verification here
		header("Location: index.php");
		echo "User data incorrect :-(";
	} else {
		die("Shouldn't be here...");
	}
}
?>

<?php
require_once(__DIR__."/core.php");

$code = mysqli_real_escape_string($con, $_POST['code']);
if (empty($code)) {
	header("Location: logincode.php?msg=empty");
	echo "Please fill in all form.";
} else {
	$status = citizen::codeLogin($code);
	if ($status == 0) {
		header("Location: logincode.php?msg=codewrong");
	} else {
		header("Location: dashboard.php");
	}
}
?>

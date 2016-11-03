<?php require("core.php"); ?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<title>Install <?=$conf["appname"];?></title>
</head>
<body>
	<div class="content">
		<article>
			<div class="text" style='margin-top:10px;'>
				<h1 style='text-align:center;'>Install <?=$conf["appname"];?>!</h1>
				<?php
				if (isset($_GET['install']) && $_GET['install'] == "1") {
					$sql = array();
					$sql["users"] = "CREATE TABLE users
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						username VARCHAR(100),
						password TEXT,
						type INT(1),
						name VARCHAR(100)
					)";
					$sql["apikeys"] = "CREATE TABLE apikeys
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						keytext VARCHAR(32) UNIQUE,
						description VARCHAR(200),
						voting INT(13),
						userid INT(13),
						status INT(2),
						uses INT(13)
					)";
					$sql["generatedcodes"] = "CREATE TABLE generatedcodes
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						code VARCHAR(16) UNIQUE,
						name TEXT,
						dni VARCHAR(9),
						birthday INT(13),
						method INT(2),
						status INT(1),
						creation INT(13),
						usercreation INT(13),
						uses INT(13),
						usesdone INT(13)
					)";
					$sql["votings"] = "CREATE TABLE votings
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						name VARCHAR(100),
						description TEXT,
						votingdescription TEXT,
						maxvotingballots INT(3),
						variable BOOLEAN,
						datebegins INT(13),
						dateends INT(13),
						status INT(2),
						filters TEXT,
						customhtml TEXT
					)";
					$sql["voting_ballots"] = "CREATE TABLE voting_ballots
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						name VARCHAR(100),
						description TEXT,
						voting INT(13),
						color VARCHAR(6)
					)";
					$sql["ballots"] = "CREATE TABLE ballots
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						ballot INT(13),
						voting INT(13),
						shahash VARCHAR(128)
					)";
					$sql["voted"] = "CREATE TABLE voted
					(
						id INT(13) NOT NULL UNIQUE,
						PRIMARY KEY(id),
						method INT(3),
						generatedcode INT(13),
						name TEXT,
						dni VARCHAR(9),
						birthday INT(13),
						voting INT(13)
					)";

					$sql["voting_defaultresults"] = "CREATE TABLE voting_defaultresults
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						voting INT(13),
						generated INT(1),
						results TEXT,
						ballotsfile VARCHAR(100)
					)";

					$sql["voting_results"] = "CREATE TABLE voting_results
					(
						id INT(13) NOT NULL AUTO_INCREMENT,
						PRIMARY KEY(id),
						voting INT(13),
						results TEXT
					)";
					$username = mysqli_real_escape_string($con, $_POST['username']);
					$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
					$name = mysqli_real_escape_string($con, $_POST['name']);
					foreach ($sql as $table_name => $query) {
						if (mysqli_query($con, $query)) {
							echo "<p style='color:green;'>Table '$table_name' created successfully.</p>";
						} else {
							die("<p style='color:red;'>Error creating table '$table_name': ".mysqli_error($con)."</p>");
						}
					}
					if(mkdir("uploads")) {
						echo "<p style='color:green;'>Folder for uploaded files created.</p>";
				  } else {
						echo "<p style='color:orange;'>Error creating the folder uploads. No permission to make it? Please, create it yourself.</p>";
				  }
					$insertuser = "INSERT INTO users (username, password, name, type) VALUES ('".$username."', '".$password."', '".$name."', 0)";
					if (mysqli_query($con, $insertuser)){
						echo "<p style='color:green;'>Admin user created.</p>";
					} else {
						die ("<p style='color:red;'>Error creating the admin user: ".mysqli_error($con)."</p>");
					}
					echo "<p style='color:orange;'>Please, delete the file install.php!</p>";
					echo "<p><a href='admin/login.php'>Go back and login with the data that you provided</a></p>";
				} else {
					// Select * from table_name limit 1 will return false if the table does not exist.
					$val = mysqli_query($con, "SELECT * FROM users LIMIT 1");
					if($val !== FALSE) {
						echo "<p>The app is already installed!</p>";
					} else {
						?>
						<p>Welcome to the installer! Fill in your admin user data and click continue to create the Database.</p>
						<form action="install.php?install=1" method="POST" id="install-form" autocomplete="off">
							<p><label for="username">Username</label>: <input type="text" name="username" id="username" required="required" maxlength="100"></p>
							<p><label for="password">Password</label>: <input type="password" name="password" id="password" required="required"></p>
							<p><label for="name">Real name</label>: <input type="text" name="name" id="name" required="required"></p>
							<p><input type="submit" value="Install now!" class="button-link"></p>
						</form>
						<?php
					}
				}
				?>
			</div>
		</article>
	</div>
</body>
</html>

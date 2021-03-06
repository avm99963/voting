<?php
// --------------------------
// IMPORTANT!
// Delete file when website is in production
// --------------------------
require_once("core.php");
exit(); // UNCOMMENT THIS LINE WHEN YOU WANT TO UNINSTALL EVERYTHING. This is just so it doesn't get executed without wanting to.
$result = mysqli_query($con, "SHOW TABLES FROM ".mysqli_real_escape_string($con, $nombre_db));
while ($row = mysqli_fetch_array($result)) {
	mysqli_query($con, "DROP TABLE ".$row[0]) or die("Error");
}
if (!rmdir("uploads")) {
	echo ("Oops, the uploaded images directory was not erased.");
}
// comprobamos que se haya iniciado la sesión
    if(isset($_SESSION['id'])) {
        session_destroy();
    }else {
        echo "No se ha hecho logout. ";
    }
echo "All deleted!";
?>

<?php
function connect_to_db() {
	$servername = "localhost";
	$username = "root";
	$password = "";

	return new mysqli($servername, $username, $password, $dbname);
}

// Check connection
$conn = connect_to_db();
if ($conn->connect_error || mysqli_connect_errno()) {
	echo '{"success": false, "error": "DB connection error"}';
	exit();
}
?>
<?php
if (isset($_COOKIE['auth'])) {
	include 'db_connect.php';

	$token = $_COOKIE['auth'];
	$preparedStatementDeleteToken = $conn->prepare("DELETE FROM auth_token WHERE token=?");
	if ($preparedStatementDeleteToken) {
		$preparedStatementDeleteToken->bind_param("s", $token);
		$preparedStatementDeleteToken->execute();
		$preparedStatementDeleteToken->close();
	}
}

header('Location: /login.php?from=logout');
?>
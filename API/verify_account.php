<?php
if (isset($_GET['token'])) {
	include 'db_connect.php';
	$token = $_GET['token'];

	$preparedStatement = $conn->prepare("UPDATE user SET verified=1 WHERE email IN (SELECT user_email FROM unverified_account WHERE token=?)");
	if ($preparedStatement) {
		$preparedStatement->bind_param("s", $token);
		$exec = $preparedStatement->execute();
		$preparedStatement->close();

		if ($exec) {
			// Set header to redirect to login page
			header("Location: /login.php?from=account_verification");

			// Remove the entry from the unverified_account table
			$preparedStatementDel = $conn->prepare("DELETE FROM unverified_account WHERE token=?");
			if ($preparedStatementDel) {
				$preparedStatementDel->bind_param("s", $token);
				$exec = $preparedStatementDel->execute();
				$preparedStatementDel->close();
				$conn->close();

				if ($exec)
					exit();
			}
		}
	}

	$conn->close();
	echo 'SQL statement error';
} else {
	echo 'No token specified';
	exit();
}
?>
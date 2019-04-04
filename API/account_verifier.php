<?php
function check($shouldRedirect = true) {
	include 'db_connect.php';

	if (isset($_COOKIE['auth'])) {
		$token = $_COOKIE['auth'];
		$exp_date = time() + 3600;
		$preparedStatementCheckToken = $conn->prepare("SELECT user_email, exp_date FROM auth_token WHERE token=?");

		if ($preparedStatementCheckToken) {
			$preparedStatementCheckToken->bind_param("s", $token);
			if ($preparedStatementCheckToken->execute()) {
				$preparedStatementCheckToken->bind_result($email, $exp_date_server);
				$fetch = $preparedStatementCheckToken->fetch();
				$preparedStatementCheckToken->close();

				if ($fetch) {
					if (strtotime($exp_date_server) >= time()) {
						// Update token expiration date
						$preparedStatementUpdateToken = $conn->prepare("UPDATE auth_token SET exp_date=FROM_UNIXTIME(?) WHERE token=?");
						if ($preparedStatementUpdateToken) {
							$preparedStatementUpdateToken->bind_param("is", $exp_date, $token);
							$preparedStatementUpdateToken->execute();
							$preparedStatementUpdateToken->close();
						}

						if ($_SERVER['REQUEST_URI'] == '/login.php') {
							if ($shouldRedirect)
								header('Location: /dashboard.php');
						} else {
							return array($conn, $email);
						}
					} else {
						// Delete entry from database
						$preparedStatementDeleteToken = $conn->prepare("DELETE FROM auth_token WHERE token=?");
						if ($preparedStatementDeleteToken) {
							$preparedStatementDeleteToken->bind_param("s", $token);
							$preparedStatementDeleteToken->execute();
							$preparedStatementDeleteToken->close();
						}
					}
				}
			}
		}
	}

	$conn->close();
	if (substr($_SERVER['REQUEST_URI'], 0, strlen('/login.php')) !== '/login.php' && $shouldRedirect) {
		header('Location: /login.php?from=token_exp');
		exit();
	}
}
?>
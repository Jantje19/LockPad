<?php
// Checking
function invalidPost($type) {
	$str = "Required field '" . $type . "' not found";

	header('Content-type: text/json');
	echo '{"success": false, "error": "' . $str . '"}';
	exit();
}

if (isset($_POST['email']))
	$email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
else
	invalidPost('email');
if (isset($_POST['pass']))
	$password = trim($_POST['pass']);
else
	invalidPost('pass');
if (isset($_POST['enc_token']))
	$enc_token = trim($_POST['enc_token']);
else
	invalidPost('enc_token');
if (isset($_POST['enc_iv']))
	$enc_iv = trim($_POST['enc_iv']);
else
	invalidPost('enc_iv');


include 'password_helper.php';
if (!checkCapcha()) {
	echo '{"success": false, "error": "Invalid capcha"}';
	exit();
}


// Actual code
function sendMail($mail, $token) {
	$headers = "From: i409738@hera.fhict.nl\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$html = file_get_contents('mail.html');
	$html = str_replace('{{href}}', 'https://i409738.hera.fhict.nl/API/verify_account.php?token=' . $token, $html);

	mail($mail, 'LockPad - Account verification', $html, $headers);
}

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	if (strlen($password) > 0 && strlen($email) > 0) {
		include 'db_connect.php';

		$password = prepareForDatabase($password);
		$preparedStatementUser = $conn->prepare("INSERT INTO user (email, password, enc_token, enc_iv) VALUES (?, ?, ?, ?)");
		if ($preparedStatementUser) {
			$preparedStatementUser->bind_param("ssss", $email, $password, $enc_token, $enc_iv);
			$exec = $preparedStatementUser->execute();
			$preparedStatementUser->close();

			if ($exec) {
				$preparedStatementUnver = $conn->prepare("SELECT token FROM unverified_account WHERE user_email=?");
				if ($preparedStatementUnver) {
					$preparedStatementUnver->bind_param("s", $email);
					if ($preparedStatementUnver->execute()) {
						$preparedStatementUnver->bind_result($token);
						$fetch = $preparedStatementUnver->fetch();
						$preparedStatementUnver->close();
						$conn->close();

						if ($fetch) {
							sendMail($email, $token);
							echo '{"success": true}';
							exit();
						} else {
							echo '{"success": false, "error": "Unable to execute SQL statement"}';
							exit();
						}
					} else {
						echo '{"success": false, "error": "Unable to execute SQL statement"}';
						$conn->close();
						exit();
					}
				} else {
					echo '{"success": false, "error": "Unable to execute SQL statement"}';
					$conn->close();
					exit();
				}
			} else {
				echo '{"success": false, "error": "The provided email address is already registerd"}';
				$conn->close();
				exit();
			}
		} else {
			echo '{"success": false, "error": "Unable to prepare SQL statement"}';
			$conn->close();
			exit();
		}

		$conn->close();
	} else {
		echo '{"success": false, "error": "A field is empty"}';
		exit();
	}
} else {
	echo '{"success": false, "error": "Invalid email"}';
	exit();
}
?>
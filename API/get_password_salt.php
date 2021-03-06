<?php
function invalidPost($type) {
	$str = "Required field '" . $type . "' not found";

	header('Content-type: text/json');
	echo '{"success": false, "error": "' . $str . '"}';
	exit();
}

if (isset($_POST['email']))
	$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
else
	invalidPost('email');

include 'account_verifier.php';
$data = check(false);
$conn = $data[0];

include 'password_helper.php';

if ($data[1] === null) {
	if (!checkCapcha()) {
		echo '{"success": false, "error": "Invalid capcha"}';
		exit();
	}
} else {
	$email = $data[1];
}

$preparedStatement = $conn->prepare("SELECT password FROM user WHERE email=?");
if ($preparedStatement) {
	$preparedStatement->bind_param("s", $email);
	if ($preparedStatement->execute()) {
		$preparedStatement->bind_result($dbPass);
		$fetch = $preparedStatement->fetch();
		$preparedStatement->close();

		if ($fetch) {
			if (strpos($dbPass, '[[PBKDF2]]') !== 0)
				echo '{"success": true, "data": false}';
			else {
				$salt = getPBKDF2Salt($dbPass);
				echo '{"success": true, "data": true, "salt": "' . $salt . '"}';
			}

			$conn->close();
			exit();
		}
	}
}

$conn->close();
echo '{"success": false, "error": "Invalid user"}';
?>
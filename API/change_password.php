<?php
include 'account_verifier.php';
$data = check();

// Checking
function invalidPost($type) {
	$str = "Required field '" . $type . "' not found";

	header('Content-type: text/json');
	echo '{"success": false, "error": "' . $str . '"}';
	exit();
}

if (isset($_POST['curr-pass']))
	$currPass = trim($_POST['curr-pass']);
else
	invalidPost('Current password');
if (isset($_POST['token']))
	$token = trim($_POST['token']);
else
	invalidPost('Token');
if (isset($_POST['enc-iv']))
	$encIv = trim($_POST['enc-iv']);
else
	invalidPost('Encryption IV');
if (isset($_POST['new-pass']))
	$newPass = trim($_POST['new-pass']);
else
	invalidPost('New password');

include 'password_helper.php';

// Actual code
if (strlen($currPass) > 0 && strlen($token) > 0 && strlen($encIv) > 0) {
	$preparedStatementGetPass = $data[0]->prepare("SELECT password FROM user WHERE email=?");
	if ($preparedStatementGetPass) {
		$preparedStatementGetPass->bind_param("s", $data[1]);
		if ($preparedStatementGetPass->execute()) {
			$preparedStatementGetPass->bind_result($dbPass);
			$fetch = $preparedStatementGetPass->fetch();
			$preparedStatementGetPass->close();

			$newPass = prepareForDatabase($newPass);
			if ($fetch) {
				if (checkPassword($currPass, $dbPass)) {
					$preparedStatementUpdatePass = $data[0]->prepare("UPDATE user SET password=?, enc_token=?, enc_iv=? WHERE email=?");
					if ($preparedStatementUpdatePass) {
						$preparedStatementUpdatePass->bind_param("ssss", $newPass, $token, $encIv, $data[1]);
						$exec = $preparedStatementUpdatePass->execute();
						$preparedStatementUpdatePass->close();
						$data[0]->close();

						if ($exec) {
							echo '{"success": true}';
							exit();
						}
					}

					echo '{"success": false, "error": "Unable to execute SQL statement"}';
					$data[0]->close();
					exit();
				} else {
					echo '{"success": false, "error": "Invalid password"}';
				}

				exit();
			}
		}
	}

	echo '{"success": false, "error": "Unable to execute SQL statement"}';
	$data[0]->close();
	exit();
} else {
	echo '{"success": false, "error": "A field is empty"}';
}

$data[0]->close();
?>
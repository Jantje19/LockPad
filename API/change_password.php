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
if (isset($_POST['new-pass']))
	$newPass = trim($_POST['new-pass']);
else
	invalidPost('New password');
if (isset($_POST['new-pass-ver']))
	$newPassVer = trim($_POST['new-pass-ver']);
else
	invalidPost('New password (Verification)');

// Actual code
if (strlen($currPass) > 0 && strlen($newPass) > 0 && strlen($newPassVer) > 0) {
	$preparedStatementGetPass = $data[0]->prepare("SELECT password FROM user WHERE email=?");
	if ($preparedStatementGetPass) {
		$preparedStatementGetPass->bind_param("s", $data[1]);
		if ($preparedStatementGetPass->execute()) {
			$preparedStatementGetPass->bind_result($dbPass);
			$fetch = $preparedStatementGetPass->fetch();
			$preparedStatementGetPass->close();

			if ($fetch) {
				if (password_verify($currPass, $dbPass)) {
					if ($newPass == $newPassVer) {
						$newPass = password_hash($newPass, PASSWORD_DEFAULT);

						if (password_verify($newPass, $dbPass)) {
							$preparedStatementUpdatePass = $data[0]->prepare("UPDATE user SET password=? WHERE email=?");

							if ($preparedStatementUpdatePass) {
								$preparedStatementUpdatePass->bind_param("ss", $newPass, $data[1]);
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
							echo '{"success": false, "error": "Password was unchanged"}';
						}
					} else {
						echo '{"success": false, "error": "Passwords are not the same"}';
					}
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
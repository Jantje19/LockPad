<?php
require_once 'ClassLoader.php';
Loader::register('./2FA-Lib/','RobThree\\Auth');

// Checking
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
if (isset($_POST['pass']))
	$password = trim($_POST['pass']);
else
	invalidPost('pass');
if (isset($_POST['twoFA']))
	$twoFA = trim($_POST['twoFA']);
else
	$twoFA = null;

// Actual code
include 'db_connect.php';

function doAuth($token, $expdate, $enc_token, $enc_iv) {
	setcookie("auth", $token, $expdate, "/");
	echo '{"success": true, "data": { "enc_token": "' . $enc_token . '", "enc_iv": "' . $enc_iv . '" }}';
	exit();
}

function isAlreadyInAuthTable($conn, $email) {
	$preparedStatementCheckToken = $conn->prepare("SELECT token, exp_date FROM auth_token WHERE user_email=?");

	if ($preparedStatementCheckToken) {
		$preparedStatementCheckToken->bind_param("s", $email);
		if ($preparedStatementCheckToken->execute()) {
			$preparedStatementCheckToken->bind_result($token, $exp_date);
			$fetch = $preparedStatementCheckToken->fetch();
			$preparedStatementCheckToken->close();

			if ($fetch) {
				$exp_date = strtotime($exp_date);
				if ($exp_date >= time()) {
					// Update token expiration date
					$exp_date = time() + 3600;
					$preparedStatementUpdateToken = $conn->prepare("UPDATE auth_token SET exp_date=FROM_UNIXTIME(?) WHERE token=?");
					if ($preparedStatementUpdateToken) {
						$preparedStatementUpdateToken->bind_param("is", $exp_date, $token);
						$preparedStatementUpdateToken->execute();
						$preparedStatementUpdateToken->close();
					}

					return array($token, $exp_date);
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

	return false;
}

$preparedStatement = $conn->prepare("SELECT verified, password, enc_token, enc_iv, `2FA_secret` FROM user WHERE email=?");
if ($preparedStatement) {
	$preparedStatement->bind_param("s", $email);
	if ($preparedStatement->execute()) {
		$preparedStatement->bind_result($verified, $dbPass, $enc_token, $enc_iv, $server2FASecret);
		$fetch = $preparedStatement->fetch();
		$preparedStatement->close();

		if ($fetch) {
			if ($verified == true) {
				if (password_verify($password, $dbPass)) {
					if ($server2FASecret) {
						if ($twoFA) {
							$timeprovider = new RobThree\Auth\Providers\Time\HttpTimeProvider();
							$tfa = new RobThree\Auth\TwoFactorAuth('LockPad', $timeprovider);

							if (!$tfa->verifyCode($server2FASecret, $twoFA)) {
								echo '{"success": false, "error": "Invalid 2FA token"}';
								$conn->close();
								exit();
							}
						} else {
							echo '{"success": false, "error": "2FA token required, but not specified", "twoFA": true}';
							$conn->close();
							exit();
						}
					}

					$data = isAlreadyInAuthTable($conn, $email);
					if ($data == false) {
						$expdate = time() + 3600;
						$token = uniqid();

						$preparedStatementToken = $conn->prepare("INSERT INTO auth_token (`token`, `user_email`, `exp_date`) VALUES (?, ?, FROM_UNIXTIME(?))");
						if ($preparedStatementToken) {
							$preparedStatementToken->bind_param("ssi", $token, $email, $expdate);
							$exec = $preparedStatementToken->execute();
							$preparedStatementToken->close();
							$conn->close();

							if ($exec)
								doAuth($token, $expdate, $enc_token, $enc_iv);
						}

						echo '{"success": false, "error": "Unable to add token to database"}';
					} else {
						doAuth($data[0], $data[1], $enc_token, $enc_iv);
					}
				} else {
					echo '{"success": false, "error": "Invalid password"}';
				}
			} else {
				echo '{"success": false, "error": "This account is not verified (yet)"}';
			}

			$conn->close();
			exit();
		}
	}
} else {
	$conn->close();
	echo '{"success": false, "error": "Email address not registerd"}';
	exit();
}

$conn->close();
echo '{"success": false, "error": "Email address not registerd"}';
?>
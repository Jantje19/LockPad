<?php
include 'account_verifier.php';
$data = check();

$stmntCheck2FAExistance = $data[0]->prepare("SELECT `2FA_secret` FROM user WHERE email=?");
if ($stmntCheck2FAExistance) {
	$stmntCheck2FAExistance->bind_param("s", $data[1]);
	if ($stmntCheck2FAExistance->execute()) {
		$stmntCheck2FAExistance->bind_result($dbSecret);
		$fetch = $stmntCheck2FAExistance->fetch();
		$stmntCheck2FAExistance->close();

		if ($fetch) {
			if ($dbSecret != null) {
				echo '2FA already enabled!';
				header('Location: /dashboard.php');
				exit();
			} else {
				if (isset($_POST['token'], $_POST['secret'])) {
					$secret = trim($_POST['secret']);
					$token = trim($_POST['token']);

					if (strlen($secret) > 0 && strlen($token) > 0) {
						require_once 'ClassLoader.php';
						Loader::register('./2FA-Lib/','RobThree\\Auth');
						$timeprovider = new RobThree\Auth\Providers\Time\HttpTimeProvider();
						$tfa = new RobThree\Auth\TwoFactorAuth('LockPad', $timeprovider);
						if ($tfa->verifyCode($secret, $token)) {
							$preparedStatementNote = $data[0]->prepare("UPDATE user SET `2FA_secret`=? WHERE email=?;");
							$preparedStatementNote->bind_param("ss", $secret, $data[1]);
							$exec = $preparedStatementNote->execute();
							$preparedStatementNote->close();
							$data[0]->close();

							if ($exec) {
								header('Location: /setuptwofactorauthentication.php?success=true');
								exit();
							}

							header('Location: /setuptwofactorauthentication.php?success=false&error=Database error');
							exit();
						}
					}
				}

				$data[0]->close();
				header('Location: /setuptwofactorauthentication.php?success=false&error=Invalid arguments');
				exit();
			}
		}
	}
}

$data[0]->close();
echo 'A fatal error occured';
header('Location: /setuptwofactorauthentication.php?success=false&error=Database error');
exit();
?>
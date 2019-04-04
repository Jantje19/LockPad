<?php
include 'API/account_verifier.php';
$data = check();

if (isset($_GET['success'])) {
	if (isset($_GET['error']) && strtolower($_GET['success']) == 'false')
		printf('<p>An error occured: %s</p>', $_GET['error']);
	else {
		echo '<p>Successfully added 2FA to your account!<p>';
		echo '<script src="/Scripts/helpers.js"></script>';
		echo '<script>logout();</script>';
		echo '<p>Redirecting...</p>';
		echo '<p>If nothing happens <a href="/API/logout.php">click here</a></p>';
	}

	exit();
}

$stmntCheck2FAExistance = $data[0]->prepare("SELECT `2FA_secret` FROM user WHERE email=?");
if ($stmntCheck2FAExistance) {
	$stmntCheck2FAExistance->bind_param("s", $data[1]);
	if ($stmntCheck2FAExistance->execute()) {
		$stmntCheck2FAExistance->bind_result($dbSecret);
		$fetch = $stmntCheck2FAExistance->fetch();
		$stmntCheck2FAExistance->close();
		$data[0]->close();

		if ($fetch) {
			if ($dbSecret) {
				echo '2FA already enabled!';
				header('Location: /dashboard.php');
				exit();
			} else {
				require_once 'API/ClassLoader.php';
				Loader::register('./2FA-Lib','RobThree\\Auth');
				$timeprovider = new RobThree\Auth\Providers\Time\HttpTimeProvider();
				$tfa = new RobThree\Auth\TwoFactorAuth('LockPad', $timeprovider);
				$secret = $tfa->createSecret();
			}
		} else {
			echo 'A fatal error occured';
			exit();
		}
	} else {
		echo 'A fatal error occured';
		exit();
	}
} else {
	echo 'A fatal error occured';
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad - Setup 2FA</title>

	<link href="Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/account.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/2FA.css" rel="stylesheet" type="text/css" media="screen"/>

	<link rel="shortcut icon" href="/Assets/Icons/favicon.ico"/>
	<link rel="shortcut icon" href="/Assets/Icons/favicon.ico" type="image/vnd.microsoft.icon"/>

	<meta name="author" content="LockPad inc." />
	<meta name="description" content="Note encryption and sync service">
	<meta name="keywords" content="notes, encryption, sync">

	<!--Let browser know website is optimized for mobile-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta name="mobile-web-app-capable" content="yes"/>

	<link rel="apple-touch-icon" sizes="57x57" href="/Assets/Icons/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/Assets/Icons/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/Assets/Icons/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/Assets/Icons/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/Assets/Icons/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/Assets/Icons/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/Assets/Icons/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/Assets/Icons/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/Assets/Icons/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="/Assets/Icons/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/Assets/Icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/Assets/Icons/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/Assets/Icons/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#27ae60">
	<meta name="msapplication-navbutton-color" content="#27ae60"/>
	<meta name="msapplication-TileImage" content="/Assets/Icons/ms-icon-144x144.png">
	<meta name="theme-color" content="#27ae60">
	<meta name="apple-mobile-web-app-status-bar-style" content="#27ae60"/>

</head>
<body>

	<main>
		<div id="frosted-glass">
			<div>
				<h1>Setup 2FA</h1>
				<p>Scan the following image with your app:</p>
				<img title="<?php echo $secret; ?>" src="<?php echo $tfa->getQRCodeImageAsDataUri($data[1], $secret); ?>">
				<details><?php echo $secret; ?></details>
				<form action="/API/setup2fa.php" method="POST">
					<input type="text" name="token" placeholder="Verify code" autofocus="autofocus">
					<input style="display: none" type="text" name="secret" value="<?php echo $secret; ?>" autocomplete="off">
					<button class="fancy-btn">Submit</button>
				</form>
				<a href="/dashboard.php">&larr; Go back</a>
			</div>
		</div>
	</main>

	<noscript>
		<div>
			<h1>JavaScript is disabled</h1>
			<p>
				JavaScript has to be anabled when using this site.<br>
				<br>
				Please enable JavaScript.<br>
				You can find out how <a href="https://www.enable-javascript.com/">here</a>.
			</p>
		</div>
	</noscript>

</body>
</html>
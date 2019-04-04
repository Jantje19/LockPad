<?php
include 'API/account_verifier.php';
$data = check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad - Login</title>

	<link href="Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/account.css" rel="stylesheet" type="text/css" media="screen"/>

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

	<script type="text/javascript" src="/Scripts/workerPromisify.js"></script>
	<script type="text/javascript" src="/Scripts/helpers.js"></script>
	<script type="text/javascript" src="/Scripts/Pages/login.js"></script>

</head>
<body>

	<main>
		<div id="frosted-glass">
			<div>
				<h1>Log in</h1>
				<p id="server_message"></p>
				<form target="#" onsubmit="event.preventDefault();">
					<input type="email" name="email" autofocus="autofocus" placeholder="E-Mail" autocomplete="username">
					<input type="password" name="pass" placeholder="Password" autocomplete="current-password">
					<button class="fancy-btn">Log in</button>
				</form>
				<form target="#" onsubmit="event.preventDefault();" id="twoFAForm">
					<input type="text" name="2fa" placeholder="Two factor token" autocomplete="one-time-code" autocomplete="off">
					<button class="fancy-btn">Log in</button>
				</form>
				<a href="/register.html">Create an account</a>
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
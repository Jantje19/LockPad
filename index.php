<?php
include 'API/account_verifier.php';
$data = check(false);

if (isset($data)) {
	header('Location: /dashboard.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad</title>

	<link href="/Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="/Stylesheets/index.css" rel="stylesheet" type="text/css" media="screen"/>

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

	<header>
		<div>
			<img alt="Logo" id="icon" src="Assets/icon.svg">
			<h1>LockPad</h1>
			<p>The secure note taking app you can take everywhere™️</p>

			<div>
				<a href="/register.html"><button class="fancy-btn">Get Started</button></a>
				<a href="/login.php"><button class="fancy-btn" id="login">Login</button></a>
			</div>
		</div>
	</header>

	<main>
		<div id="about" class="splitview-container">
			<div>
				<h2>About</h2>
				<p>
					LockPad is "A note taking app you can take everywhere™️".
					<br>
					<br>
					With the power of the web you can access your notes everywhere at every time on every device with a web browser.
					<br>
					You can even use this app offline!
				</p>
			</div>
			<div>
				<img alt="Site preview on phone" src="Assets/PhoneBrowser.png">
			</div>
		</div>
		<div id="security" class="splitview-container">
			<div>
				<h2>Security</h2>
				<p>
					With LockPad, every note you create gets encrypted on your device before it gets sent to the server.
					<br>
					<br>
					This means that nodbody except you (if you're not sharing notes) can access these notes. Not even us.
				</p>
			</div>
			<div>
				<img alt="Logo" src="Assets/icon.svg">
			</div>
		</div>
		<div id="opensource" class="splitview-container">
			<div>
				<h2>Open Source</h2>
				<p>
					LockPad is entirely opensource. This way you can varify and ensure that what we say happens is actually happening and that our code is secure.
					<br>
					<br>
					Go ahead take a look!
				</p>
				<a target="_blank" href="https://github.com/jantje19/LockPad">
					<button class="fancy-btn">GitHub</button>
				</a>
			</div>
			<div>
				<a target="_blank" href="https://github.com/jantje19/LockPad">
					<img alt="GitHub" src="Assets/ic_github_black.svg">
				</a>
			</div>
		</div>
	</main>

</body>
</html>
<?php
include 'API/account_verifier.php';
$data = check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad - Dashboard - Import</title>

	<link href="Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/dashboard.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/import.css" rel="stylesheet" type="text/css" media="screen"/>

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
	<script type="text/javascript" src="/Scripts/toast.js"></script>

	<script type="text/javascript" src="/Scripts/Pages/import.js"></script>

</head>
<body>

	<header>
		<div>
			<button class="dash-fancy" title="Cancel" id="cancel-btn"><img src="/Assets/ic_cancel_white.svg"></button>
		</div>
		<div>
			<i id="status"></i>
		</div>
	</header>

	<main>
		<input type="file" accept=".json,.txt,text/plain,text/json,application/json">
		<div id="upload-section">
			<svg viewBox="0 0 24 27" width="20" height="20">
				<path d=" M 17 11 L 17 9 C 17 6.24 14.76 4 12 4 C 9.24 4 7 6.24 7 9 L 7 11 L 17 11 Z  M 9 9 C 9 7.34 10.34 6 12 6 C 13.66 6 15 7.34 15 9 L 15 11 L 9 11 L 9 9 Z "/>
				<path d=" M 18 11 L 6 11 C 4.9 11 4 11.9 4 13 L 4 23 C 4 24.1 4.9 25 6 25 L 18 25 C 19.1 25 20 24.1 20 23 L 20 13 C 20 11.9 19.1 11 18 11 Z  M 18 23 L 6 23 L 6 13 L 18 13 L 18 23 Z "/>
			</svg>
			<p>Drag a file or click here to import a note</p>
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
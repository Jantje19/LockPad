<?php
include 'API/account_verifier.php';
$data = check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad - Dashboard - Create note</title>

	<link href="Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/markdown.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/dashboard.css" rel="stylesheet" type="text/css" media="screen"/>

	<link rel="shortcut icon" href="/Assets/Icons/favicon.ico"/>
	<link rel="shortcut icon" href="/Assets/Icons/favicon.ico" type="image/vnd.microsoft.icon"/>

	<meta name="author" content="LockPad inc." />
	<meta name="description" content="Note encryption and sync service">
	<meta name="keywords" content="notes, encryption, sync">

	<!--Let browser know website is optimized for mobile-->
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"/> -->
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

	<script type="text/javascript" src="/Scripts/localforage.min.js"></script>
	<script type="text/javascript" src="/Scripts/workerPromisify.js"></script>
	<script type="text/javascript" src="/Scripts/Pages/createnote.js"></script>

</head>
<body>

	<header>
		<div>
			<button class="dash-fancy" title="Cancel" id="cancel-btn"><img src="Assets/ic_cancel_white.svg"></button>
			<!-- <button class="dash-fancy" title="Save"><img src="Assets/ic_save_white.svg"></button> -->
		</div>
		<div>
			<i id="status"></i>
		</div>
	</header>

	<main>
		<textarea id="textarea"></textarea>
		<div id="markdown" class="markdown-body"><i>Parsed markdown will appear here</i></div>
	</main>

</body>
</html>
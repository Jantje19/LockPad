<?php
include 'API/account_verifier.php';
$data = check();
$conn = $data[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad - Dashboard</title>

	<link href="Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/loader.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/markdown.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/dashboard.css" rel="stylesheet" type="text/css" media="screen"/>

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

	<script type="text/javascript" src="/Scripts/localforage.min.js"></script>
	<script type="text/javascript" src="/Scripts/workerPromisify.js"></script>
	<script type="text/javascript" src="/Scripts/helpers.js"></script>
	<script type="text/javascript" src="/Scripts/toast.js"></script>

	<script type="text/javascript" src="/Scripts/Pages/dashboard.js"></script>

</head>
<body>

	<header>
		<div>
			<button class="dash-fancy" id="add-btn" title="Add note"><img alt="Add icon" src="Assets/ic_add_white.svg"></button>
			<button class="dash-fancy" id="delete-btn" title="Delete selected notes"><img alt="Delete icon" src="Assets/ic_delete_white.svg"></button>
		</div>
		<div>
			<input id="search" type="text" placeholder="Search">
		</div>
		<div>
			<button id="user-icon" aria-label="Open account management pop-up menu">
				<img alt="User icon" src="https://gravatar.com/avatar/<?php echo md5(strtolower($data[1])) ?>?d=https://identicon-1132.appspot.com/<?php echo hash('sha256', strtolower($data[1])); ?>" />
			</button>

			<div id="user-popup">
				<h3><?php echo $data[1]; ?></h3>
				<a href="/changepassword.php"><button>Change password</button></a>
				<a href="/setuptwofactorauthentication.php"><button>Setup 2FA-authentication</button></a>
				<a href="/import.php"><button>Import notes</button></a>
				<button id="logout-btn">Log out</button>
			</div>
		</div>
	</header>

	<?php
	$preparedStatement = $conn->prepare("SELECT password FROM user WHERE email=?");
	if ($preparedStatement) {
		$preparedStatement->bind_param("s", $data[1]);
		if ($preparedStatement->execute()) {
			$preparedStatement->bind_result($dbPass);
			$fetch = $preparedStatement->fetch();
			$preparedStatement->close();

			if ($fetch) {
				if (strpos($dbPass, '[[PBKDF2]]') !== 0)
					echo '<div id="pass-message"><button id="pass-message-close" class="dash-fancy" title="Close">❌</button><h1>Warning</h1><p>You haven\'t upgraded to the newer (more secure) password management system.<br>To be more secure, please <a href="/changepassword.php">change your password</a>.</p></div>';
			}
		}
	}
	?>

	<main>
		<div id="local-save">
			<div class="loader"></div>
		</div>
		<div id="server-save">
			<?php
			$sql = "SELECT note_id, title, enc_iv_title, enc_iv_content, content FROM note WHERE note_id IN ( SELECT note_id FROM owner WHERE user_email=? )";
			$preparedStatementNotes = $conn->prepare($sql);

			$html = '<div disabled="disabled" titleIv="[[titleIv]]" contentIv="[[contentIv]]" class="note server" id="note-[[id]]" role="button" tabindex="-1"><input aria-label="Toggle note selection" type="checkbox" tabindex="-1"><button aria-label="Grow note to fullscreen" tabindex="-1"><img title="Grow note" alt="grow icon" src="/Assets/ic_grow_black.svg"></button><h3>[[title]]</h3><div class="markdown-body">[[content]]</div></div>';

			if ($preparedStatementNotes) {
				$preparedStatementNotes->bind_param("s", $data[1]);
				if ($preparedStatementNotes->execute()) {
					$result = $preparedStatementNotes->get_result();
					$preparedStatementNotes->close();

					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							$newHtml = str_replace('[[id]]', $row['note_id'], $html);
							$newHtml = str_replace('[[content]]', $row['content'], $newHtml);
							$newHtml = str_replace('[[contentIv]]', $row['enc_iv_content'], $newHtml);

							if (isset($row['title'])) {
								$newHtml = str_replace('[[title]]', $row['title'], $newHtml);
								$newHtml = str_replace('[[titleIv]]', $row['enc_iv_title'], $newHtml);
							} else {
								$newHtml = str_replace('[[title]]', '<i>Untitled</i>', $newHtml);
								$newHtml = str_replace('[[titleIv]]', '😒', $newHtml);
							}

							echo $newHtml;
						}
					} else {
						echo '<p>You have no server saved notes.<br><a href="/createnote.php">Go ahead and create one</a>.</p>';
					}
				}
			}

			$conn->close();
			?>
		</div>
	</main>

	<button id="addtohomescreen-btn" class="fancy-btn" title="Add this page to your device's homescreen to use it as an app">Add to homescreen</button>

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
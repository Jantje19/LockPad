<?php
include 'API/account_verifier.php';
$data = check();

if (!isset($_GET['token']) || !isset($_GET['type']))
	header('Location: /dashboard.php');

$token = str_replace('➕', '+', $_GET['token']); // The '+' character has to be replaced because PHP can't handle that for some reason
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>LockPad - Dashboard - Edit note</title>

	<link href="Stylesheets/main.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/markdown.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/dashboard.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="Stylesheets/edit.css" rel="stylesheet" type="text/css" media="screen"/>

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
	<script type="text/javascript">
		// Get the tokens from PHP
		const token = '<?php echo $token ?>';
		const type = '<?php echo $_GET['type']?>';
	</script>
	<script type="text/javascript" src="/Scripts/Pages/edit.js"></script>

</head>
<body>

	<div id="export-popup">
		<div>
			<h2>Export</h2>
			<div class="toggle">
				<span>Plain</span>
				<input id="toggle-save-type" type="checkbox">
				<label for="toggle-save-type"></label>
				<span>Encrypted</span>
			</div>
			<button class="fancy-btn" id="export-save">Save</button>
		</div>
	</div>

	<header>
		<div>
			<button class="dash-fancy" title="Cancel" id="cancel-btn"><img src="Assets/ic_cancel_white.svg"></button>
			<button class="dash-fancy" title="Save" id="save-btn" disabled="disabled"><img src="Assets/ic_save_white.svg"></button>
			<button class="dash-fancy" title="Export" id="export-btn" disabled="disabled"><img src="Assets/ic_import_export_white.svg"></button>
		</div>
		<div>
			<i id="status"></i>
		</div>
	</header>

	<?php
	$title = '';
	$titleIv = '';
	$content = '';
	$contentIv = '';
	$server = false;

	if ($_GET['type'] == 'server') {
		$server = true;
		$conn = $data[0];
		$preparedStatementCheck = $conn->prepare("SELECT user_email FROM owner WHERE note_id=?");
		$err = true;

		if ($preparedStatementCheck) {
			$preparedStatementCheck->bind_param("s", $token);
			if ($preparedStatementCheck->execute()) {
				$preparedStatementCheck->bind_result($note_email);
				$fetch = $preparedStatementCheck->fetch();
				$preparedStatementCheck->close();

				if ($fetch) {
					if ($note_email == $data[1]) {
						$preparedStatementData = $conn->prepare("SELECT title, content, enc_iv_title, enc_iv_content, creation_date, edited_date FROM note WHERE note_id=?");

						if ($preparedStatementData) {
							$preparedStatementData->bind_param("s", $token);
							if ($preparedStatementData->execute()) {
								$preparedStatementData->bind_result($title, $content, $titleIv, $contentIv, $creation, $edited);
								$fetch = $preparedStatementData->fetch();
								$preparedStatementData->close();

								if ($fetch)
									$err = false;
							}
						}
					}
				}
			}
		}

		if ($err) {
			echo 'Error!';
			$conn->close();
			echo '<script>window.location = "/dashboard.php";</script>';
		}

		$conn->close();
	}
	?>

	<main>
		<div id="textarea-container">
			<input type="text" <?php echo (($server == true) ? 'titleIv="' . $titleIv . '"' : '') ?> id="title" placeholder="Title" disabled="disabled" value="<?php echo $title; ?>">
			<textarea id="textarea" disabled="disabled" <?php echo (($server == true) ? 'contentIv="' . $contentIv . '"' : '') ?>><?php echo $content; ?></textarea>
		</div>
		<div id="markdown" class="markdown-body"><i>Parsed markdown will appear here</i></div>
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
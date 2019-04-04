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

if (isset($_POST['content']))
	$content = trim($_POST['content']);
else
	invalidPost('content');
if (isset($_POST['content-iv']))
	$contentIv = trim($_POST['content-iv']);
else
	invalidPost('content-iv');
if (isset($_POST['edited']))
	$edited = trim($_POST['edited']);
else
	invalidPost('edited');

if (isset($_POST['title'])) {
	if (isset($_POST['title-iv'])) {
		$titleIv = trim($_POST['title-iv']);
		$title = trim($_POST['title']);
	} else {
		invalidPost('title-iv');
	}
} else {
	$titleIv = null;
	$title = null;
}

if (isset($_POST['id'])) {
	$id = trim($_POST['id']);
} else {
	$id = null;
}


// Actual code
function handleNew($conn, $email, $title, $titleIv, $content, $contentIv, $edited) {
	if ($title)
		$sqlNote = "INSERT INTO note (title, content, enc_iv_title, enc_iv_content, creation_date, edited_date) VALUES (?,?,?,?,FROM_UNIXTIME(?),FROM_UNIXTIME(?));";
	else
		$sqlNote = "INSERT INTO note (content, enc_iv_content, creation_date, edited_date) VALUES (?,?,FROM_UNIXTIME(?),FROM_UNIXTIME(?));";

	$time = time();
	$preparedStatementNote = $conn->prepare($sqlNote);
	if ($preparedStatementNote) {
		if ($title)
			$preparedStatementNote->bind_param("ssssii", $title, $content, $titleIv, $contentIv, $time, intval($edited));
		else
			$preparedStatementNote->bind_param("ssii", $content, $contentIv, $time, intval($edited));

		$exec = $preparedStatementNote->execute();
		$preparedStatementNote->close();

		if ($exec) {
			$sqlOwner = "INSERT INTO owner (user_email, note_id) VALUES (?, (SELECT note_id FROM note WHERE note_id=LAST_INSERT_ID()));";
			$preparedStatementOwner = $conn->prepare($sqlOwner);
			if ($preparedStatementOwner) {
				$preparedStatementOwner->bind_param("s", $email);
				$exec = $preparedStatementOwner->execute();
				$preparedStatementOwner->close();
				$conn->close();

				if ($exec) {
					echo '{"success": true}';
					exit();
				}
			}

			// Delete note
			$conn->query("DELETE FROM note WHERE note_id=LAST_INSERT_ID()");
			$conn->close();
			echo '{"success": false}';
			exit();
		}
	}

	$conn->close();
	echo '{"success": false, "error": "SQL statement error"}';
	exit();
}

function handleExisting($conn, $email, $id, $title, $titleIv, $content, $contentIv, $edited) {
	$preparedStatementCheckOwnership = $conn->prepare("SELECT user_email FROM owner WHERE note_id=?");

	if ($preparedStatementCheckOwnership) {
		$preparedStatementCheckOwnership->bind_param("s", $id);
		if ($preparedStatementCheckOwnership->execute()) {
			$preparedStatementCheckOwnership->bind_result($result_email);
			$fetch = $preparedStatementCheckOwnership->fetch();
			$preparedStatementCheckOwnership->close();
			$time = time();

			if ($fetch) {
				// Check if user is owner of the note
				if ($result_email == $email) {
					if ($title)
						$sqlNote = "UPDATE note SET title=?, enc_iv_title=?, content=?, enc_iv_content=?, edited_date=FROM_UNIXTIME(?) WHERE note_id=?;";
					else
						$sqlNote = "UPDATE note SET content=?, enc_iv_content=?, edited_date=FROM_UNIXTIME(?) WHERE note_id=?;";

					$preparedStatementNote = $conn->prepare($sqlNote);
					if ($preparedStatementNote) {
						if ($title)
							$preparedStatementNote->bind_param("ssssis", $title, $titleIv, $content, $contentIv, $time, $id);
						else
							$preparedStatementNote->bind_param("ssis", $content, $contentIv, $time, $id);
					}
					$exec = $preparedStatementNote->execute();
					$preparedStatementNote->close();

					if ($exec) {
						$conn->close();
						echo '{"success": true}';
						exit();
					} else {
						$conn->close();
						echo '{"success": false, "error": "SQL statement error"}';
						exit();
					}
				}
			}

			$conn->close();
			echo '{"success": false, "error": "Account not associated with note"}';
			exit();
		}
	}

	$conn->close();
	echo '{"success": false, "error": "SQL statement error"}';
	exit();
}

if (strlen($content) > 0 && strlen($contentIv) > 0 && strlen($edited) > 0) {
	// Check title
	if ($title) {
		if (strlen($title) <= 0 || strlen($titleIv) <= 0) {
			echo '{"success": false, "error": "Invalid arguments"}';
			$data[0]->close();
			exit();
		}
	}

	if ($id)
		handleExisting($data[0], $data[1], $id, $title, $titleIv, $content, $contentIv, $edited);
	else
		handleNew($data[0], $data[1], $title, $titleIv, $content, $contentIv, $edited);
} else {
	$data[0]->close();
	echo '{"success": false, "error": "Invalid arguments"}';
	exit();
}

$data[0]->close();
echo '{"success": false, "error": "Server error"}';
?>

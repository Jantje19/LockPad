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

if (isset($_POST['notes']))
	$noteIds = trim($_POST['notes']);
else
	invalidPost('notes');

// Actual code
function deleteNote($conn, $email, $id) {
	$preparedStatementCheckAccess = $conn->prepare("SELECT user_email FROM owner WHERE note_id=?");
	if ($preparedStatementCheckAccess) {
		$preparedStatementCheckAccess->bind_param("s", $id);
		if ($preparedStatementCheckAccess->execute()) {
			$preparedStatementCheckAccess->bind_result($dbEmail);
			$fetch = $preparedStatementCheckAccess->fetch();
			$preparedStatementCheckAccess->close();


			if ($fetch) {
				if ($dbEmail == $email) {
					$preparedStatementDelOwnwer = $conn->prepare("DELETE FROM owner WHERE user_email=? AND note_id=?");
					if ($preparedStatementDelOwnwer) {
						$preparedStatementDelOwnwer->bind_param("ss", $email, $id);
						$exec = $preparedStatementDelOwnwer->execute();
						$preparedStatementDelOwnwer->close();

						if ($exec) {
							$preparedStatementDelNote = $conn->prepare("DELETE FROM note WHERE note_id=?");
							if ($preparedStatementDelNote) {
								$preparedStatementDelNote->bind_param("s", $id);
								$exec = $preparedStatementDelNote->execute();
								$preparedStatementDelNote->close();

								if ($exec)
									return true;
							}
						}
					}
				}
			}
		}
	}

	echo 'THIS' . $id;
	return false;
}

if (strlen($noteIds) > 0) {
	if (preg_match('/((\d+\,?)+)?(\d+)/', $noteIds)) {
		$noteIds = explode(",", $noteIds);

		foreach ($noteIds as $id)
			deleteNote($data[0], $data[1], $id);

		echo '{"success": true}';
		$data[0]->close();
		exit();
	}
}

echo '{"success": false, "error": "Invalid data supplied"}';
$data[0]->close();
exit();
?>
<?php
function removePrefix($pass)
{
	$from = '/'.preg_quote('[[PBKDF2]]', '/').'/';
	return preg_replace($from, '', $pass, 1);
}

function isPBKDF2($pass) {
	return (strpos($pass, '[[PBKDF2]]') === 0);
}

function getSplitPass($pass) {
	return explode("-", removePrefix($pass), 2);
}

function getPBKDF2Salt($pass) {
	if (!isPBKDF2($pass))
		return null;

	return getSplitPass($pass)[0];
}

function prepareForDatabase($pass) {
	if (!isPBKDF2($pass))
		return password_hash($pass, PASSWORD_DEFAULT);
	else {
		$arr = getSplitPass($pass);
		return '[[PBKDF2]]' . $arr[0] . '-' . password_hash($arr[1], PASSWORD_DEFAULT);
	}
}

function checkPassword($pass, $dbPass) {
	if (isPBKDF2($pass))
		$pass = getSplitPass($pass)[1];

	if (isPBKDF2($dbPass))
		$dbPass = getSplitPass($dbPass)[1];

	return password_verify($pass, $dbPass);
}

function checkCapcha() {
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => '[[RECAPTCHA_SECRET]]',
		'response' => $_POST["g-recaptcha-response"]
	);
	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);

	error_reporting(0);
	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);
	$captcha_success = json_decode($verify);
	return $captcha_success->success;
}
?>
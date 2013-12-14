<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(0);

require_once('session.php');

$session = new Session();

try {
	$session->get_from_cookie();
} catch (SessionException $e) {
	header('Location: ./');
}

if ($session->get_money() < 1000000000) {
	echo 'Недостаточно денег.';
} else {
	echo 'key9d97ca92b443358f8d92';
}

?>
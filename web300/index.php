<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(0);

require_once('session.php');

$session = new Session();

try {
	$session->get_from_cookie();
	header('Location: ./messages.php');
} catch (SessionException $e) {
	if (isset($_POST['name']) and ($_POST['name'] != '')) {
		$session->set_username($_POST['name']);
		$session->flush();
		header('Location: ./messages.php');
	}
}

?>

<!DOCTYPE html>
<html>
<head>
        <title>web300</title>
        <meta charset="utf-8">
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<form method="post" style="width:400px; height:40px; text-align:center; position:absolute; left:50%; margin-left:-200px; margin-top: 80px;">

Введите ваше имя, чтобы войти в чат.
<br>
<br>

<div style="height:55px;">
<input type="text" name="name" style="width:300px; margin:0;">
<input type="submit" value="Войти" class="btn btn-primary" style="width:70px;">
</div>

</form>

</body>
</html>
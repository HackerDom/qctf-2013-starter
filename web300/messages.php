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

$database = "qctf_web";
$hostname = "localhost";
$username = 'user_web300';
$password = 'cd0886e66e540e4f453cc06b09e92927';

$connection = mysql_connect($hostname, $username, $password);
mysql_select_db($database, $connection);

if (isset($_POST['message'])) {
	if ($session->pay_message()) {
		$name = mysql_real_escape_string($session->get_name());
		$text = mysql_real_escape_string(mb_substr((string)($_POST['message']), 0, 300));
		mysql_query("INSERT INTO messages (name,text) VALUES ('$name','$text')", $connection);
	}
}

$money = $session->get_money();

$session->flush();

/* select top 30 messages */
$result = mysql_query("SELECT name,text FROM messages ORDER BY id DESC LIMIT 30", $connection);

?>

<!DOCTYPE html>
<html>
<head>
        <title>web300</title>
        <meta charset="utf-8">
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="other/style.css" rel="stylesheet">
</head>
<body>

<div class="wrap" style="padding-top: 20px;">

<div style="width:415px; margin:auto; margin-bottom:10px; text-align:right;">
<a style="float: left; color: gray;" href="exit.php">Выход</a>
<a href="premium.php">Активировать премиум аккаунт [$1.000.000.000]</a>
</div>

<form method="post" style="width:415px; height:130px; text-align:right; margin-top: 20px; border-bottom: 1px solid #ccc; margin:auto;">

<textarea name="message" style="width:400px; height: 60px;"></textarea>
<span style="float:left; color: gray; font-size: 10px;">$10 каждую минуту!</span>
<span style="margin-right:10px;"><?php echo $money.'$'; ?></span><input type="submit" value="Отправить [$5]" class="btn btn-primary" style="width:130px;">

</form>

<?php while ($row = mysql_fetch_assoc($result)) { ?>

<div class="message"><span class="name">

<?php echo htmlspecialchars($row['name']).':'; ?>

</span><span class="text">

<?php echo htmlspecialchars($row['text']); ?>

</span></div>

<?php } ?>

</div>

</body>
</html>
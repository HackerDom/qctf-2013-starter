<?php

error_reporting(0);

$database = "qctf_web";
$hostname = "localhost";
$username = 'user_web100';
$password = '282b4697238e9f3e2e1fc7e79fbb11a2';

$id = $_GET['id'];

$dbhandle = mysql_connect($hostname, $username, $password) or die(mysql_error());
$selected = mysql_select_db($database, $dbhandle) or die(mysql_error());
@$result = mysql_query("SELECT url FROM images WHERE id=$id", $dbhandle) or die('MySQL error');

$a = @mysql_fetch_array($result) or die('error');

$file = $a[0];
$type = 'text/plain';
if (preg_match('/\.(jpg|jpeg)$/', $file))
	$type = 'image/jpeg';
	
$file = preg_replace('/[\\\\\\/]/', '', $file);
header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file) or die('file not found ['.$file.']');

?>
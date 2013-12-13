<?php
header('Content-Type: text/html; charset=utf-8');

require_once('./db_connect.php');

$dbhandle = mysql_connect($hostname, $root_username, $root_password) or die(mysql_error());

$Q =
"
DROP USER 'user_web100'
DELETE FROM mysql.tables_priv WHERE User='user_web100'
CREATE user 'user_web100'@'%' IDENTIFIED BY '282b4697238e9f3e2e1fc7e79fbb11a2'
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_web100', 'images', 'root', CURRENT_TIMESTAMP, 'SELECT', 'SELECT')

DROP USER 'user_web300'
DELETE FROM mysql.tables_priv WHERE User='user_web300'
CREATE user 'user_web300'@'%' IDENTIFIED BY 'cd0886e66e540e4f453cc06b09e92927'
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_web300', 'messages', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_web300', 'sessions', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT,UPDATE', 'SELECT,INSERT,UPDATE')

FLUSH PRIVILEGES

DROP DATABASE IF EXISTS $database
CREATE DATABASE $database
";

$A = explode("\n", $Q);

foreach ($A as &$query) {
	if ($query == '') continue;
    mysql_query($query);
	if ($E = mysql_error())
		echo $query.' : '.$E.'<br>';
}

mysql_select_db($database, $dbhandle) or die(mysql_error());

$Q =
"
CREATE TABLE images (id int(11), url char(100))
INSERT INTO images (id, url) VALUES (1, 'cat.jpg'), (2, 'kitty.jpg'), (3, 'xxx.jpg'), (4, 'sadcat.jpg')

CREATE TABLE sessions (sessid char(32), money int(20))

CREATE TABLE messages (id int(11) NOT NULL AUTO_INCREMENT, name varchar(30), text varchar(300), PRIMARY KEY (id))
";

$A = explode("\n", $Q);
foreach ($A as &$query) {
	if ($query == '') continue;
    mysql_query($query);
	if ($E = mysql_error())
		echo $query.' : '.$E.'<br>';
}

?>
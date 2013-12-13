<?php
header('Content-Type: text/html; charset=utf-8');

require_once('./db_connect.php');

$dbhandle = mysql_connect($hostname, $root_username, $root_password) or die(mysql_error());

$Q =
"
DROP USER 'user_ppc200'
DELETE FROM mysql.tables_priv WHERE User='user_ppc200'
CREATE user 'user_ppc200'@'%' IDENTIFIED BY 'b9d3618777f53989159c31aa1478a758'
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level1', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level2', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level3', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level4', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level5', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level6', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level7', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')
INSERT INTO mysql.tables_priv VALUES('%', '$database', 'user_ppc200', 'sudoku_level8', 'root', CURRENT_TIMESTAMP, 'SELECT,INSERT', 'SELECT,INSERT')

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

?>
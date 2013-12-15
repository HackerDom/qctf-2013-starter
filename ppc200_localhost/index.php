<?php
header('Content-Type: text/html; charset=utf-8');
@session_start();

/* ФУНКЦИИ */

function get_new_sudoku($level) {
	$database = "qctf_ppc";
	$hostname = "localhost";
	$username = 'user_ppc200';
	$password = 'b9d3618777f53989159c31aa1478a758';
	//
	$connection = mysql_connect($hostname, $username, $password);
	mysql_select_db($database, $connection);
	$result = mysql_query("SELECT cells FROM sudoku_level${level}", $connection);
	$random = rand(0, mysql_num_rows($result) - 1);
	$result = mysql_query("SELECT cells FROM sudoku_level${level} LIMIT ${random},1", $connection);
	$result = mysql_fetch_assoc($result);
	return $result['cells'];
}

function reduce_sudoku($sudoku) {
	$empty_cells = array();
	for ($i = 0; $i < 81; ++$i)
		$empty_cells[] = $i;
	shuffle($empty_cells);
	$empty_cells = array_slice($empty_cells, 0, 40);
	for ($i = 0; $i < 40; ++$i) {
		$sudoku[$empty_cells[$i]] = '0';
	}
	return $sudoku;
}

function test_sudoku($sudoku, $mask, $data) {
	if (!preg_match('/^[1-9]{81}$/', $sudoku))
		return false;
	for ($i = 0; $i < 81; ++$i) {
		if ($data[$i] === '0')
			continue;
		if ($sudoku[$i] != $data[$i]) {
			return false;
		}
	}
	$test_groups = array();
	for ($i = 0; $i < 27; ++$i)
		$test_groups[] = array();
	for ($i = 0; $i < 9; ++$i) {
		for ($j = 0; $j < 9; ++$j) {
			$test_groups[$i][] = (int)($sudoku[$i * 9 + $j]);
		}
		for ($j = 0; $j < 9; ++$j) {
			$test_groups[$j + 9][] = (int)($sudoku[$j * 9 + $i]);
		}
		for ($j = 0; $j < 9; ++$j) {
			$test_groups[18 + ((int)($mask[$i * 9 + $j]) - 1)][] = (int)($sudoku[$i * 9 + $j]);
		}
	}
	for ($i = 0; $i < 27; ++$i) {
		if (count($test_groups[$i]) !== 9) {
			return false;
		}
		for ($j = 1; $j <= 9; ++$j)
			if (!in_array($j, $test_groups[$i])) {
				return false;
			}
	}
	return true;
}

/* --------------------- */

$levels = array( // МАСКИ УРОВНЕЙ
	'111222233111222333111223333444555666444555666444555666777888999777888999777888999',
	'111222333111222333111222633444555663444555666444555666777888999777888999777888999',
	'111222333111222333111222333444555666444555666444555966777888996777888999777888999',
	'111222333111222333111222333444555666444555666444555666777888899777888999777889999',
	'111222333111222333111222333444555666444555666444555666777788999777888999778888999',
	'111222333111222333111222333444555666444555666744555666774888999777888999777888999',
	'111222333111222333411222333441555666444555666444555666777888999777888999777888999',
	'111122333111222333112222333444555666444555666444555666777888999777888999777888999'
);

$messages = array(
	'<div id="badmessage">Решение верное, но время истекло!</div>',
	'<div id="badmessage">Решение неверное!</div>',
	'<div id="goodmessage">Решение верное!</div>',
	'<div id="goodmessage">Поехали!</div>'
);

if (
		isset($_SESSION['level']) and
		isset($_GET['solution']) and
		is_string($_GET['solution'])
   )
{
	$delay = time() - $_SESSION['time'];
	if (test_sudoku($_GET['solution'], $levels[$_SESSION['level'] - 1], $_SESSION['data'])) {
		if ($delay < 2) {
			if ($_SESSION['level'] === 8) {
				die('keyc1335e22834c27554f55');
			}
			//
			$message = $messages[2];
			//
			$level = $_SESSION['level'] + 1;
			$_SESSION['level'] += 1;
			$sudoku = get_new_sudoku($level);
			$_SESSION['sudoku'] = $sudoku;
			$string = reduce_sudoku($sudoku);
			$_SESSION['data'] = $string;
			$_SESSION['time'] = time();
			$mask = $levels[$level - 1];
		} else {
			$message = $messages[0];
			//
			$level = 1;
			$_SESSION['level'] = 1;
			$sudoku = get_new_sudoku(1);
			$_SESSION['sudoku'] = $sudoku;
			$string = reduce_sudoku($sudoku);
			$_SESSION['data'] = $string;
			$_SESSION['time'] = time();
			$mask = $levels[$level - 1];
		}
	} else {
		$message = $messages[1];
		//
		$level = 1;
		$_SESSION['level'] = 1;
		$sudoku = get_new_sudoku(1);
		$_SESSION['sudoku'] = $sudoku;
		$string = reduce_sudoku($sudoku);
		$_SESSION['data'] = $string;
		$_SESSION['time'] = time();
		$mask = $levels[$level - 1];
	}
} else {
	$message = $messages[3];
	//
	$level = 1;
	$_SESSION['level'] = 1;
	$sudoku = get_new_sudoku(1);
	$_SESSION['sudoku'] = $sudoku;
	$string = reduce_sudoku($sudoku);
	$_SESSION['data'] = $string;
	$_SESSION['time'] = time();
	$mask = $levels[$level - 1];
}

$color = array(
	'1' => 'red',
	'2' => 'green',
	'3' => 'blue',
	'4' => 'yellow',
	'5' => 'violet',
	'6' => 'lightblue',
	'7' => 'white',
	'8' => 'brown',
	'9' => 'pink'
);

?>
<!DOCTYPE html>
<html>
<head>
	<title>ppc</title>
	<meta charset="utf-8">
	<script src="other/jquery-2.0.3.min.js"></script>
	<script src="other/script.js"></script>
	<link rel="stylesheet" type="text/css" href="other/style.css"/>
</head>
<body><?php if (isset($_GET['help_me_please_t0_parse'])) echo "\n<!-- mask : ".$mask." -->\n<!-- sudoku : ".$string." -->\n"; ?>

	<div id="content">
		<div id="header">
			<div id="level">Уровень: <?php echo $level ?> из <?php echo count($levels) ?></div>
			<?php echo $message ?>
			
		</div>
		<div id="sudoku">
<?php
for ($i = 0; $i < 9; ++$i) {
	for ($j = 0; $j < 9; ++$j) {
		if ($string[$i * 9 + $j] === '0') {
?>
			<div class="unlocked piece<?php echo ' '.$color[$mask[$i * 9 + $j]] ?>"></div>
<?php
		} else {
?>
			<div class="piece<?php echo ' '.$color[$mask[$i * 9 + $j]] ?>"><?php echo $string[$i * 9 + $j] ?></div>
<?php
		}
	}
}
?>
		</div>
		<div id="send">Отправить решение</div>
    </div>
</body>
</html>
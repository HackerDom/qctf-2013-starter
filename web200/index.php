<?php
header('Content-Type: text/html; charset=utf-8');
if ($_SERVER['REMOTE_ADDR'] == '90.157.117.82') setcookie("flag", 'yes_you_found_a_flag', time() + (10 * 365 * 24 * 60 * 60));
// error_reporting(0);
?>

<?php // количество файлов
$d = dir('images');
$n = 0;
while ($d->read() !== false) {
	++$n;
}
$n -= 2; // . and ..
?>

<?php // загрузка файлов на сервер
if (isset($_FILES['uploadFile']) and $_FILES['uploadFile']['tmp_name']) {
	$imageinfo = getimagesize($_FILES['uploadFile']['tmp_name']);
	if($imageinfo['mime'] == 'image/jpeg') {
		$n++;
		move_uploaded_file($_FILES['uploadFile']['tmp_name'], 'images/image'.$n.'.jpg');
	}
}
?>

<?php // функции
function get_description($filename) {
	$b = false;
	$d = 'no description';
	if ($exif = exif_read_data($filename, 0, true)) {
		if (isset($exif['IFD0'])) {
			if (isset($exif['IFD0']['ImageDescription'])) {
				$b = true;
				$d = $exif['IFD0']['ImageDescription'];
			}
		}
	}
	$color = $b ? 'white' : 'gray';
	$class = $b ? '' : ' none';
	$d = '<div class="description'.$class.'">'.mb_substr($d, 0, 20).'</div>';
	return $d;
}
?>

<!DOCTYPE html>
<html>
<head>
        <title>web100</title>
        <meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="other/style.css"/>
		<script src="other/script.js"></script>
</head>
<body>

<form enctype="multipart/form-data" method="post">
	<div class="fileform">
		<div id="fileformlabel"></div>
		<div class="selectbutton">Обзор</div>
		<input type="file" name="uploadFile" id="upload" onchange="getName(this.value);" />
	</div>
	<input type="submit" value="Отправить" class="sendbutton">
</form>

<div id="gallery">

<?php
for ($i = 1; $i <= $n; ++$i) {
?>
	<div class="photo">
		<img src="images/image<?php echo $i ?>.jpg">
		<?php echo get_description('images/image'.$i.'.jpg'); ?>
	</div>
<?php
}
?>
</div>

</body>
</html>
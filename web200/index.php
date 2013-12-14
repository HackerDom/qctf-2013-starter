<?php // key for webbot : keya4fbceac9133049ceb93
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
if (isset($_GET['gallery']) and is_string($_GET['gallery']) and preg_match('/^[0-9]{21}$/', $_GET['gallery'])) {
	$gallery_folder = 'gallery/'.$_GET['gallery'];
	if (!file_exists($gallery_folder)) {
		header('Location: ./new.php');
	}
} else {
	header('Location: ./new.php');
}
?>

<?php // количество файлов
$d = dir($gallery_folder);
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
		move_uploaded_file($_FILES['uploadFile']['tmp_name'], $gallery_folder.'/image'.$n.'.jpg');
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
        <title>Галерея</title>
        <meta charset="utf-8">
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="other/style.css"/>
		<script src="other/jquery-2.0.3.min.js"></script>
		<script src="other/script.js"></script>
</head>
<body>

<form enctype="multipart/form-data" method="post">
	<div class="fileform">
		<div id="fileformlabel"></div>
		<div class="btn btn-primary selectbutton" id="selectbutton">Обзор</div>
	</div>
	<input type="file" name="uploadFile" id="upload" onchange="getName(this.value);">
	<input type="submit" value="Отправить" class="btn btn-success" id="sendbutton">
	<a href="new.php" class="exit btn btn-danger">Новая галерея</a>
</form>

<div id="gallery">

<?php
for ($i = 1; $i <= $n; ++$i) {
?>
	<div class="photo">
		<img src="<?php echo $gallery_folder.'/image'.$i.'.jpg'; ?>">
		<?php echo get_description($gallery_folder.'/image'.$i.'.jpg'); ?>
	</div>
<?php
}
?>
</div>

</body>
</html>
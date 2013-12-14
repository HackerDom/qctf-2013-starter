<?php
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
if ($_SERVER['REMOTE_ADDR'] == '90.157.117.82') setcookie("flag", 'keya4fbceac9133049ceb93', time() + (10 * 365 * 24 * 60 * 60));
@session_start();
$gallery_folder = 'gallery/'.session_id();
if (!file_exists($gallery_folder)) {
mkdir($gallery_folder);
copy('images/image1.jpg', $gallery_folder.'/image1.jpg');
copy('images/image2.jpg', $gallery_folder.'/image2.jpg');
copy('images/image3.jpg', $gallery_folder.'/image3.jpg');
copy('images/image4.jpg', $gallery_folder.'/image4.jpg');
copy('images/image5.jpg', $gallery_folder.'/image5.jpg');
copy('images/image6.jpg', $gallery_folder.'/image6.jpg');
copy('images/image7.jpg', $gallery_folder.'/image7.jpg');
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
	<a href="exit.php" class="exit btn btn-danger">Новая галерея</a>
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

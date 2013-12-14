<?php
$gallery_id = rand(1000000, 9999999).rand(1000000, 9999999).rand(1000000, 9999999);
$gallery_folder = 'gallery/'.$gallery_id;
mkdir($gallery_folder);
copy('images/image1.jpg', $gallery_folder.'/image1.jpg');
copy('images/image2.jpg', $gallery_folder.'/image2.jpg');
copy('images/image3.jpg', $gallery_folder.'/image3.jpg');
copy('images/image4.jpg', $gallery_folder.'/image4.jpg');
copy('images/image5.jpg', $gallery_folder.'/image5.jpg');
copy('images/image6.jpg', $gallery_folder.'/image6.jpg');
copy('images/image7.jpg', $gallery_folder.'/image7.jpg');
header('Location: ./?gallery='.$gallery_id);
?>

<!DOCTYPE html>
<html>
<head></head>
<body>
</body>
</html>
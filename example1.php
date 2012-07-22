<?php
    require_once('compare_images.class.php');
?>

<!DOCTYPE html>
<html>
<head>
  <title>Compare image test</title>
  <style type="text/css">
  /*<![CDATA[*/
    body { font-size:12px; font-family: Arial; }
  /*]]>*/
  </style>
</head>
<body>

<?php
    $image1 = 'images/kot1.jpg';
    $image2 = 'images/kot3.jpg';
?>

image 1 (<?=$image1?>) and
image 2 (<?=$image2?>) is
<?php if (compare_images::compare($image1, $image2)):?>
    identical
<?php else: ?>
    not identical
<?php endif;?>

<hr />

<?php
    $image1 = 'images/kot1.jpg';
    $image2 = 'images/kot2.jpg';
?>

image 1 (<?=$image1?>) and
image 2 (<?=$image2?>) is
<?php if (compare_images::compare($image1, $image2)):?>
    identical
<?php else: ?>
    not identical
<?php endif;?>

</body>

</html>
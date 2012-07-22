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
    tr.ident td { background-color:yellow;}
    small { display:block; text-align:center; }
    img { max-width:200px; }
  /*]]>*/
  </style>
</head>
<body>

<?php
    $images = glob('images/*.*');

    $hash_map = array();
    foreach ($images as $file)
    {
        $hash_map[md5($file)] = compare_images::get_image_hash($file);
    }

    echo '<table border="1">
            <tr>
                <th>Image 1</th>
                <th>Image 2</th>
                <th>Hamming</th>
            </tr>
    ';

    $found=0;
    $already_checked=array();
    foreach ($images as $ind1=>$image1_file)
    {
        if (!empty($already_checked[md5($image1_file)])) continue;
        foreach ($images as $ind2=>$image2_file)
        {
            if ($ind1==$ind2) continue;
            if (!empty($already_checked[md5($image2_file)])) continue;
            $image1_hash = $hash_map[md5($image1_file)];
            $image2_hash = $hash_map[md5($image2_file)];
            $compare = compare_images::compare_hash_ext($image1_hash, $image2_hash);

            $image1_filename = preg_replace('|^.+/([^/]+)$|','$1',$image1_file);
            $image2_filename = preg_replace('|^.+/([^/]+)$|','$1',$image2_file);

            echo '
                <tr class="'.($compare['hamming']<100?'ident':'').'">
                    <td valign="top">
                        <small>'.$image1_filename .'</small>
                        <img src="images/'.$image1_filename.'" />
                    </td>
                    <td valign="top">
                        <small>'.$image2_filename .'</small>
                        <img src="images/'.$image2_filename.'" />
                    </td>
                    <td align="center">
                        '.$compare['hamming'].'
                    </td>
                </tr>
            ';
       }
       $already_checked[md5($image1_file)]='ok';
    }
?>
</body>

</html>
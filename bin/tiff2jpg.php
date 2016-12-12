<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Corelog;

$filename = $_GET["name"];
$path = "file/document/";
$pathThumbs = $path . "thumbs/";
$file = $path . $filename;
try {
  // Saving every page of a TIFF separately as a JPG thumbnail
  $images = new Imagick($file);
  foreach ($images as $i => $thumb) {
    // Providing 0 forces thumbnailImage to maintain aspect ratio
    $thumb->setResolution(204, 98);
    $thumb->resampleImage(98, 98, imagick::FILTER_UNDEFINED, 1);
    // $thumb->thumbnailImage(300,0); show full size
    $thumb->setImageCompression(imagick::COMPRESSION_JPEG);
    $thumb->setImageCompressionQuality(90);
    $thumb->writeImage($pathThumbs . $filename . $i . ".jpg");

    echo "<center><br/>Page" . ($i + 1) . "<br/><img src='$pathThumbs$filename$i.jpg' alt='images' border=1></img></center>";
  }
  $images->clear();
  $images->destroy();
} catch (Exception $e) {
  Corelog::log($e->getMessage(), Corelog::ERROR);
}

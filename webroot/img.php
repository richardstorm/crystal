<?php 
require(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'CImage' . DIRECTORY_SEPARATOR . 'CImage.php');

// Create an instance of CImage
$img = new CImage();

// Output image
$img->outputImage();

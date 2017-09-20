<?php
$file = substr($_SERVER['REQUEST_URI'],1);
$path = dirname($file);
if (!file_exists($path))
    @mkdir($path, 0755, true);

$target = $file;
$comps = explode('/',$file);
$original = implode('/',array_slice($comps,3)); 

chdir(dirname(__FILE__));

$thumbWidth = $comps[1];
$thumbHeight = $comps[2];




// Double the size for retina devices
if ($retina) {
  if ($thumbWidth) $thumbWidth *= 2;
  if ($thumbHeight) $thumbHeight *= 2;
  $original = str_replace('@2x', '', $original);
}

// Check the original file exists
if (!is_file($original)) {
  die('File doesn\'t exist');
}

// Make sure the file doesn't exist already
if (!file_exists($target)) {

  // Make sure we have enough memory
  ini_set('memory_limit', 128*1024*1024);

  // Get the current size & file type
  list($width, $height, $type) = getimagesize($original);

  // Load the image
  switch ($type) {
    case IMAGETYPE_GIF:
      $image = imagecreatefromgif($original);
      break;

    case IMAGETYPE_JPEG:
      $image = imagecreatefromjpeg($original);
      break;

    case IMAGETYPE_PNG:
      $image = imagecreatefrompng($original);
      break;

    default:
      die("Invalid image type (#{$type} = " . image_type_to_extension($type) . ")");
  }

  // Calculate height automatically if not given
  if ($thumbHeight === null) {
    $thumbHeight = round($height * $thumbWidth / $width);
  }

  // Ratio to resize by
  $widthProportion = $thumbWidth / $width;
  $heightProportion = $thumbHeight / $height;
  $proportion = max($widthProportion, $heightProportion);

  // Area of original image that will be used
  $origWidth = floor($thumbWidth / $proportion);
  $origHeight = floor($thumbHeight / $proportion);

  // Co-ordinates of original image to use
  $x1 = floor($width - $origWidth) / 2;
  $y1 = floor($height - $origHeight) / 2;

  // Resize the image
  $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
  imagecopyresampled($thumbImage, $image, 0, 0, $x1, $y1, $thumbWidth, $thumbHeight, $origWidth, $origHeight);

  // Save the new image
  switch ($type)
  {
    case IMAGETYPE_GIF:
      imagegif($thumbImage, $target);
      break;

    case IMAGETYPE_JPEG:
      imagejpeg($thumbImage, $target, 60);
      break;

    case IMAGETYPE_PNG:
      imagepng($thumbImage, $target,9);
      break;

    default:
      throw new LogicException;
  }

  // Make sure it's writable
  chmod($target, 0666);

  // Close the files
  imagedestroy($image);
  imagedestroy($thumbImage);
}

// Send the file header
$data = getimagesize($original);
if (!$data) {
  die("Cannot get mime type");
} else {
  header('Content-Type: ' . $data['mime']);
}

// Send the file to the browser
readfile($target);
<?php
/**
 * This is a PHP class to process images using PHP GD.
 *
 */
class CImage {

  /**
   * Members
   */
  private $maxWidth;
  private $maxHeight;
  
  private $src;
  private $verbose;
  private $saveAs;
  private $quality;
  private $ignoreCache;
  private $newWidth;
  private $newHeight;
  private $cropToFit;
  
  private $sharpen;
  private $grayscale;
  private $sepia;

  private $pathToImage;
  
  private $imgInfo;
  private $filesize;
  
  private $width;
  private $height;
  private $type;
  private $attr;
  
  private $cropWidth;
  private $cropHeight;
  
  private $fileExtension;
  
  private $image;
  private $cacheFileName;
  
  
  /**
   * Constructor...
   *
   */
  public function __construct() {
    //
    // Ensure error reporting is on
    //
    error_reporting(-1);              // Report all type of errors
    ini_set('display_errors', 1);     // Display all errors 
    ini_set('output_buffering', 0);   // Do not buffer outputs, write directly



    //
    // Define some constant values, append slash
    // Use DIRECTORY_SEPARATOR to make it work on both windows and unix.
    //
    define('IMG_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'webroot' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
    define('CACHE_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'webroot' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

    $this->maxWidth = $this->maxHeight = 2000;
    $this->getAndValidateIncomingArguments();
    
    if ($this->verbose) $this->displayLog();
    
    $this->getInfo();
    $this->calculateNewDimensions();
    $this->createCacheFileName();
    
    if (!$this->isValidCache()) {
      $this->openOriginalImage();
      $this->resize();
      $this->applyFilters();
      $this->saveAs();
    }
  }
  
  
  /**
   * Get and validate the incoming arguments
   * 
   */
  private function getAndValidateIncomingArguments() {
    // Get the incoming parameters
    $src          = isset($_GET['src'])         ? $_GET['src']      : null;
    $saveAs       = isset($_GET['save-as'])     ? $_GET['save-as']  : null;
    $quality      = isset($_GET['quality'])     ? $_GET['quality']  : 60;
    $newWidth     = isset($_GET['width'])       ? $_GET['width']    : null;
    $newHeight    = isset($_GET['height'])      ? $_GET['height']   : null;
    $cropToFit    = isset($_GET['crop-to-fit']) ? true              : null;

    $pathToImage = realpath(IMG_PATH . $src);
    
    // Validate input
    is_dir(IMG_PATH) or $this->errorMessage('The image dir (' . IMG_PATH . ') is not a valid directory.');
    is_writable(CACHE_PATH) or $this->errorMessage('The cache dir (' . CACHE_PATH . ') is not a writable directory.');
    isset($src) or $this->errorMessage('Must set src-attribute.');
    preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $src) or $this->errorMessage('Filename contains invalid characters.');
    substr_compare(IMG_PATH, $pathToImage, 0, strlen(IMG_PATH)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
    is_null($saveAs) or in_array($saveAs, array('png', 'jpg', 'jpeg', 'gif')) or $this->errorMessage('Not a valid extension to save image as');
    is_null($quality) or (is_numeric($quality) and $quality > 0 and $quality <= 100) or $this->errorMessage('Quality out of range');
    is_null($newWidth) or (is_numeric($newWidth) and $newWidth > 0 and $newWidth <= $this->maxWidth) or $this->errorMessage('Width out of range');
    is_null($newHeight) or (is_numeric($newHeight) and $newHeight > 0 and $newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
    is_null($cropToFit) or ($cropToFit and $newWidth and $newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
    
    // Save parameters
    $this->src          = $src;
    $this->saveAs       = $saveAs;
    $this->quality      = $quality;
    $this->newWidth     = $newWidth;
    $this->newHeight    = $newHeight;
    $this->cropToFit    = $cropToFit;
    $this->pathToImage  = $pathToImage;
    
    // Get and save parameters that doesn't need to be validated
    $this->verbose      = isset($_GET['verbose'])   ? true  : null;
    $this->ignoreCache  = isset($_GET['no-cache'])  ? true  : null;
    $this->sharpen      = isset($_GET['sharpen'])   ? true  : null;
    $this->grayscale    = isset($_GET['grayscale']) ? true  : null;
    $this->sepia        = isset($_GET['sepia'])     ? true  : null;
  }


  /**
   * Display error message.
   *
   * @param string $message the error message to display.
   */
  private function errorMessage($message) {
    header("Status: 404 Not Found");
    die('img.php says 404 - ' . htmlentities($message));
  }


  /**
   * Display log message.
   *
   * @param string $message the log message to display.
   */
  private function verbose($message) {
    echo "<p>" . htmlentities($message) . "</p>";
  }


  /**
   * Sharpen image as http://php.net/manual/en/ref.image.php#56144
   * http://loriweb.pair.com/8udf-sharpen.html
   *
   * @param resource $image the image to apply this filter on.
   * @return resource $image as the processed image.
   */
  private function sharpenImage($image) {
    $matrix = array(
      array(-1,-1,-1,),
      array(-1,16,-1,),
      array(-1,-1,-1,)
    );
    $divisor = 8;
    $offset = 0;
    imageconvolution($image, $matrix, $divisor, $offset);
    return $image;
  }
  
  
  /**
   * Applies a grayscale filter.
   
   * @param resource $image the image to apply this filter on.
   * @return resource $image as the processed image.
   */
  private function applyGrayscale($image) {
    imagefilter($image, IMG_FILTER_GRAYSCALE);
    return $image;
  }
  
  
  /**
   * Applies a sepia filter.
   
   * @param resource $image the image to apply this filter on.
   * @return resource $image as the processed image.
   */
  private function applySepia($image) {
    $this->image = $this->applyGrayscale($this->image);
    imagefilter($image, IMG_FILTER_BRIGHTNESS, -10);
    imagefilter($image, IMG_FILTER_CONTRAST, -20);
    imagefilter($image, IMG_FILTER_COLORIZE, 120, 60, 0, 0);
    $this->image = $this->sharpenImage($this->image);
    return $image;
  }


  /**
   * Start displaying log & create url to current image.
   * 
   */
  private function displayLog() {
    $query = array();
    parse_str($_SERVER['QUERY_STRING'], $query);
    unset($query['verbose']);
    $url = '?' . http_build_query($query);

    echo <<<EOD
  <html lang='en'>
  <meta charset='UTF-8'/>
  <title>img.php verbose mode</title>
  <h1>Verbose mode</h1>
  <p><a href=$url><code>$url</code></a><br>
  <img src='{$url}' /></p>
EOD;
  }


  /**
   * Get information on the image.
   * 
   */
  private function getInfo() {
    $this->imgInfo = list($this->width, $this->height, $this->type, $this->attr) = getimagesize($this->pathToImage);
    !empty($this->imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
    $mime = $this->imgInfo['mime'];
    
    // Display information about the current image
    if ($this->verbose) {
      $this->filesize = filesize($this->pathToImage);
      $this->verbose("Image file: {$this->pathToImage}");
      $this->verbose("Image information: " . print_r($this->imgInfo, true));
      $this->verbose("Image width x height (type): {$this->width} x {$this->height} ({$this->type}).");
      $this->verbose("Image file size: {$this->filesize} bytes.");
      $this->verbose("Image mime type: {$mime}.");
    }
  }


  /**
   * Calculate new dimensions for the image.
   * 
   */
  private function calculateNewDimensions() {
    $aspectRatio = $this->width / $this->height;

    if ($this->cropToFit && $this->newWidth && $this->newHeight) {
      $targetRatio = $this->newWidth / $this->newHeight;
      $this->cropWidth = $targetRatio > $aspectRatio ? $this->width : round($this->height * $targetRatio);
      $this->cropHeight = $targetRatio > $aspectRatio ? round($this->width  / $targetRatio) : $this->height;
      if ($this->verbose) { 
        $this->verbose("Crop to fit into box of {$this->newWidth}x{$this->newHeight}. Cropping dimensions: {$this->cropWidth}x{$this->cropHeight}."); 
      }
    }
    else if ($this->newWidth && !$this->newHeight) {
      $this->newHeight = round($this->newWidth / $aspectRatio);
      if ($this->verbose) { 
        $this->verbose("New width is known {$this->newWidth}, height is calculated to {$this->newHeight}."); 
      }
    }
    else if (!$this->newWidth && $this->newHeight) {
      $this->newWidth = round($this->newHeight * $aspectRatio);
      if ($this->verbose) { 
        $this->verbose("New height is known {$this->newHeight}, width is calculated to {$this->newWidth}."); 
      }
    }
    else if ($this->newWidth && $this->newHeight) {
      $ratioWidth  = $this->width  / $this->newWidth;
      $ratioHeight = $this->height / $this->newHeight;
      $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
      $this->newWidth  = round($this->width  / $ratio);
      $this->newHeight = round($this->height / $ratio);
      if ($this->verbose) { 
        $this->verbose("New width & height is requested, keeping aspect ratio results in {$this->newWidth}x{$this->newHeight}."); 
      }
    }
    else {
      $this->newWidth = $this->width;
      $this->newHeight = $this->height;
      if ($this->verbose) { 
        $this->verbose("Keeping original width & heigth."); 
      }
    }
  }


  /**
   * Create a filename for the cache.
   * 
   */
  private function createCacheFileName() {
    $parts                = pathinfo($this->pathToImage);
    $this->fileExtension  = $parts['extension'];
    $this->saveAs         = is_null($this->saveAs) ? $this->fileExtension : $this->saveAs;
    $quality_             = is_null($this->quality) ? null : "_q{$this->quality}";
    $cropToFit_           = is_null($this->cropToFit) ? null : "_cf";
    $sharpen_             = is_null($this->sharpen) ? null : "_sh";
    $grayscale_           = is_null($this->sharpen) ? null : "_gs";
    $sepia_               = is_null($this->sharpen) ? null : "_se";
    $dirName              = preg_replace('/\//', '-', dirname($this->src));
    $this->cacheFileName  = CACHE_PATH . "-{$dirName}-{$parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}{$grayscale_}{$sepia_}.{$this->saveAs}";
    $this->cacheFileName  = preg_replace('/^a-zA-Z0-9\.-_/', '', $this->cacheFileName);

    if ($this->verbose) { 
      $this->verbose("Cache file is: {$this->cacheFileName}"); 
    }
  }


  /**
   * If there is a valid image in the cache directory.
   * 
   * @return boolean true if cached image is valid
   */
  private function isValidCache() {
    $imageModifiedTime = filemtime($this->pathToImage);
    $cacheModifiedTime = is_file($this->cacheFileName) ? filemtime($this->cacheFileName) : null;

    // If cached image is valid.
    if(!$this->ignoreCache && is_file($this->cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
      if ($this->verbose) { 
        $this->verbose("Cache file is valid, output it."); 
        return true;
      }
    }

    if ($this->verbose) { 
      $this->verbose("Cache is not valid, process image and create a cached version of it."); 
    }
    return false;
  }


  /**
   * Open up the original image from file.
   * 
   */
  private function openOriginalImage() {
    if ($this->verbose) { 
      $this->verbose("File extension is: {$this->fileExtension}"); 
    }

    switch($this->fileExtension) {  
      case 'jpg':
      case 'jpeg': 
        $this->image = imagecreatefromjpeg($this->pathToImage);
        if ($this->verbose) { 
          $this->verbose("Opened the image as a JPEG image."); 
        }
        break;  
      
      case 'png':  
        $this->image = imagecreatefrompng($this->pathToImage); 
        if ($this->verbose) { 
          $this->verbose("Opened the image as a PNG image."); 
        }
        break;
      
      case 'gif':  
        $this->image = imagecreatefromgif($this->pathToImage); 
        if ($this->verbose) { 
          $this->verbose("Opened the image as a GIF image."); 
        }
        break;  

      default: errorPage('No support for this file extension.');
    }
  }


  /**
   * Resize the image if needed.
   * 
   */
  private function resize() {
    if ($this->cropToFit) {
      if ($this->verbose) { 
        $this->verbose("Resizing, crop to fit."); 
      }
      $cropX = round(($this->width - $this->cropWidth) / 2);  
      $cropY = round(($this->height - $this->cropHeight) / 2);    
      $imageResized = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
      imagecopyresampled($imageResized, $this->image, 0, 0, $cropX, $cropY, $this->newWidth, $this->newHeight, $this->cropWidth, $this->cropHeight);
      $this->image  = $imageResized;
      $this->width  = $this->newWidth;
      $this->height = $this->newHeight;
    }
    else if(!($this->newWidth == $this->width && $this->newHeight == $this->height)) {
      if ($this->verbose) { 
        $this->verbose("Resizing, new height and/or width."); 
      }
      $imageResized = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
      imagecopyresampled($imageResized, $this->image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
      $this->image  = $imageResized;
      $this->width  = $this->newWidth;
      $this->height = $this->newHeight;
    }
  }

  
  /**
   * Create new image and keep transparency.
   *
   * @param integer $width the image width.
   * @param integer $height the image height.
   * @return resource $image as the processed image.
   */
  private function createImageKeepTransparency($width, $height) {
      $img = imagecreatetruecolor($width, $height);
      imagealphablending($img, false);
      imagesavealpha($img, true);  
      return $img;
  }

  /**
   * Apply filters and postprocessing of image.
   * 
   */
  private function applyFilters() {
    if ($this->sharpen) {
      $this->image = $this->sharpenImage($this->image);
    }
    if ($this->grayscale) {
      $this->image = $this->applyGrayscale($this->image);
    }
    if ($this->sepia) {
      $this->image = $this->applySepia($this->image);
    }
  }


  /**
   * Save the image.
   * 
   */
  private function saveAs() {
    switch ($this->saveAs) {
      case 'jpeg':
      case 'jpg':
        if($this->verbose) { 
          $this->verbose("Saving image as JPEG to cache using quality = {$this->quality}."); 
        }
        imagejpeg($this->image, $this->cacheFileName, $this->quality);
      break;  

      case 'png':  
        if($this->verbose) { 
          $this->verbose("Saving image as PNG to cache."); 
        }
        // Turn off alpha blending and set alpha flag
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagepng($this->image, $this->cacheFileName);
      break;
      
      case 'gif':  
        if($this->verbose) { 
          $this->verbose("Saving image as GIF to cache."); 
        }
        imagegif($this->image, $this->cacheFileName);
      break; 

      default:
        $this->errorMessage('No support to save as this file extension.');
      break;
    }
    
    if ($this->verbose) { 
      clearstatcache();
      $cacheFilesize = filesize($this->cacheFileName);
      $this->verbose("File size of cached file: {$cacheFilesize} bytes."); 
      $this->verbose("Cache file has a file size of " . round($cacheFilesize/$this->filesize*100) . "% of the original size.");
    }
  }

  
  /**
   * Output an image together with last modified header.
   *
   */
  public function outputImage() {
    $info = getimagesize($this->cacheFileName);
    !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
    $mime   = $info['mime'];

    $lastModified = filemtime($this->cacheFileName);  
    $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

    if ($this->verbose) {
      $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
      $this->verbose("Memory limit: " . ini_get('memory_limit'));
      $this->verbose("Time is {$gmdate} GMT.");
    }

    if (!$this->verbose) {
      header('Last-Modified: ' . $gmdate . ' GMT');
    }
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) {
      if ($this->verbose) { 
        $this->verbose("Would send header 304 Not Modified, but its verbose mode."); 
      }
      else {
        header('HTTP/1.0 304 Not Modified');
      }
    } 
    else {  
      if ($this->verbose) { 
        $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); 
      }
      else {
        header('Content-type: ' . $mime);  
        readfile($this->cacheFileName);
      }
    }
  }
}
<?php

if (false) {

    class Imagick
    {

        public const FILTER_BOX = 1;

        public function getImageFormat()
        {
            return '';
        }

        public function coalesceImages()
        {
            return new Imagick();
        }

        public function nextImage()
        {
            return true;
        }

        public function resizeImage()
        {
        }

        public function deconstructImages()
        {
            return new Imagick();
        }

        public function clear()
        {
        }

        public function destroy()
        {
        }

        public function writeImages()
        {
        }
    }
}
function convertThumbsIfNotExists($source, $destination)
{
    global $advancedCustom;
    if (file_exists($destination)) {
        _error_log("convertThumbsIfNotExists destination image exists ");
        return true;
    }
    if (!file_exists($source)) {
        _error_log("convertThumbsIfNotExists source image does not exists ");
        return false;
    }
    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::loadPlugin("CustomizeAdvanced");
    }
    $width = 300;
    $height = 300;
    $orientation = getImageOrientation($source);

    if ($orientation == "landscape") {
        $width = $advancedCustom->thumbsWidthLandscape;
        $height = $advancedCustom->thumbsHeightLandscape;
    } else if ($orientation == "portrait") {
        $width = $advancedCustom->thumbsWidthPortrait;
        $height = $advancedCustom->thumbsHeightPortrait;
    }

    return convertImageIfNotExists($source, $destination, $width, $height, true);
}

function getImageOrientation($imagePath)
{
    // Get the image dimensions
    $imageSize = getimagesize($imagePath);

    // Check the width and height
    $width = $imageSize[0];
    $height = $imageSize[1];

    // Determine the orientation
    if ($width > $height) {
        return "landscape";
    } else if ($width < $height) {
        return "portrait";
    } else {
        return "square";
    }
}

/**
 * Check whether an image is fully transparent.
 *
 * @param string $filename The path to the image file.
 * @return bool True if the image is fully transparent, false otherwise.
 */
function is_image_fully_transparent($filename)
{
    if(filesize($filename)>10000){
        return false;
    }
    // Load the image
    $image = imagecreatefrompng($filename);

    // Get the number of colors in the image
    $num_colors = imagecolorstotal($image);

    // Loop through each color and check if it's fully transparent
    $is_transparent = true;
    for ($i = 0; $i < $num_colors; $i++) {
        $color = imagecolorsforindex($image, $i);
        if ($color['alpha'] != 127) { // 127 is the maximum value for a fully transparent color
            $is_transparent = false;
            break;
        }
    }

    // Free up memory
    imagedestroy($image);

    // Return the result
    return $is_transparent;
}

function totalImageColors($image_path)
{
    $img = imagecreatefromjpeg($image_path);
    $w = imagesx($img);
    $h = imagesy($img);

    // capture the raw data of the image
    _ob_start();
    imagegd2($img, null, $w);
    $data = _ob_get_clean();
    $totalLength = strlen($data);

    // calculate the length of the actual pixel data
    // from that we can derive the header size
    $pixelDataLength = $w * $h * 4;
    $headerLength = $totalLength - $pixelDataLength;

    // use each four-byte segment as the key to a hash table
    $counts = [];
    for ($i = $headerLength; $i < $totalLength; $i += 4) {
        $pixel = substr($data, $i, 4);
        $count = &$counts[$pixel];
        $count += 1;
    }
    $colorCount = count($counts);
    return $colorCount;
}

function isImageCorrupted($image_path)
{
    $fsize = filesize($image_path);
    if (strpos($image_path, 'thumbsSmall') !== false) {
        if ($fsize < 1000) {
            return true;
        }
    } else {
        if ($fsize < 2000) {
            return true;
        }
    }

    if (totalImageColors($image_path) === 1) {
        return true;
    }

    if (!isGoodImage($image_path)) {
        return true;
    }
    return false;
}

// detect partial grey immages
function isGoodImage($fn)
{
    [$w, $h] = getimagesize($fn);
    $im = imagecreatefromstring(file_get_contents($fn));
    $grey = 0;
    for ($i = 0; $i < 5; ++$i) {
        for ($j = 0; $j < 5; ++$j) {
            $x = $w - 5 + $i;
            $y = $h - 5 + $j;
            //[$r, $g, $b] = array_values(imagecolorsforindex($im, imagecolorat($im, $x, $y)));
            [$r, $g, $b] = imagecolorsforindex($im, imagecolorat($im, $x, $y));
            if ($r == $g && $g == $b && $b == 128) {
                ++$grey;
            }
        }
    }
    return $grey < 12;
}

function defaultIsPortrait()
{
    global $_defaultIsPortrait;

    if (!isset($_defaultIsPortrait)) {
        $_defaultIsPortrait = false;
        $obj = AVideoPlugin::getDataObjectIfEnabled('YouPHPFlix2');
        if (!empty($obj) && empty($obj->landscapePosters)) {
            $_defaultIsPortrait = true;
        }
    }

    return $_defaultIsPortrait;
}

function defaultIsLandscape()
{
    return !defaultIsPortrait();
}


function fileIsAnValidImage($filepath)
{
    if (file_exists($filepath)) {
        if (filesize($filepath) === 42342) {
            return false;
        } else if (!function_exists('exif_imagetype')) {
            if ((list($width, $height, $type, $attr) = getimagesize($filepath)) !== false) {
                return $type;
            }
        } else {
            return exif_imagetype($filepath);
        }
    }
    return false;
}

/**
 * return true if de file was deleted or does not exits and false if the file still present on the system
 * @param string $filepath
 * @return boolean
 */
function deleteInvalidImage($filepath)
{
    if (file_exists($filepath)) {
        if (!fileIsAnValidImage($filepath)) {
            _error_log("deleteInvalidImage($filepath)");
            unlink($filepath);
            return true;
        }
        return false;
    }
    return true;
}


function isImage($file)
{
    [$width, $height, $type, $attr] = getimagesize($file);
    if ($type == IMAGETYPE_PNG) {
        return 'png';
    }
    if ($type == IMAGETYPE_JPEG) {
        return 'jpg';
    }
    if ($type == IMAGETYPE_GIF) {
        return 'gif';
    }
    return false;
}

function convertImageToOG($source, $destination)
{
    if (!file_exists($destination)) {
        $w = 200;
        $h = 200;
        $sizes = getimagesize($source);
        if ($sizes[0] < $w || $sizes[1] < $h) {
            $tmpDir = getTmpDir();
            $fileConverted = $tmpDir . "_jpg_" . uniqid() . ".jpg";
            convertImage($source, $fileConverted, 100);
            im_resize($fileConverted, $destination, $w, $h, 100);
            //_error_log("convertImageToOG ($destination) unlink line=".__LINE__);
            @unlink($fileConverted);
        }
    }
    return $destination;
}

function convertImageToRoku($source, $destination)
{
    return convertImageIfNotExists($source, $destination, 1280, 720, true);
}

function convertImageIfNotExists($source, $destination, $width, $height, $scaleUp = true)
{
    if (empty($source)) {
        _error_log("convertImageIfNotExists: source image is empty");
        return false;
    }
    $source = str_replace(['_thumbsSmallV2'], [''], $source);
    if (!file_exists($source)) {
        //_error_log("convertImageIfNotExists: source does not exists {$source}");
        return false;
    }
    if (empty(filesize($source))) {
        _error_log("convertImageIfNotExists: source has filesize 0");
        return false;
    }
    $mime = mime_content_type($source);
    if ($mime == 'text/plain') {
        _error_log("convertImageIfNotExists error, image in wrong format/mime type {$source} " . file_get_contents($source));
        unlink($source);
        return false;
    }
    if (file_exists($destination) && filesize($destination) > 1024) {
        $sizes = getimagesize($destination);
        if ($sizes[0] < $width && $sizes[1] < $height) {
            _error_log("convertImageIfNotExists: $destination, w=$width, h=$height file is smaller unlink " . json_encode($sizes));
            unlink($destination);
            return false;
        }
    }
    if (!file_exists($destination)) {
        //_error_log("convertImageIfNotExists($source, $destination, $width, $height)");
        try {
            $tmpDir = getTmpDir();
            $fileConverted = $tmpDir . "_jpg_" . uniqid() . ".jpg";
            convertImage($source, $fileConverted, 100);
            if (file_exists($fileConverted)) {
                if ($scaleUp) {
                    scaleUpImage($fileConverted, $fileConverted, $width, $height);
                }
                if (file_exists($fileConverted)) {
                    im_resize($fileConverted, $destination, $width, $height, 100);
                    if (!file_exists($destination)) {
                        _error_log("convertImageIfNotExists: [$fileConverted] [$source] [$destination]");
                    }else{
                        _error_log("convertImageIfNotExists: ($source, $destination) line=".__LINE__);
                    }
                } else {
                    _error_log("convertImageIfNotExists: convertImage error 1 $source, $fileConverted");
                }
            } else {
                _error_log("convertImageIfNotExists: convertImage error 2 $source, $fileConverted");
            }
            @unlink($fileConverted);
        } catch (Exception $exc) {
            _error_log("convertImageIfNotExists: " . $exc->getMessage());
            return false;
        }
    }
    return $destination;
}

function getVideoImagewithHoverAnimation($relativePath, $relativePathHoverAnimation = '', $title = '', $preloadImage = false, $doNotUseAnimatedGif = false)
{
    $id = uniqid();
    //getImageTagIfExists($relativePath, $title = '', $id = '', $style = '', $class = 'img img-responsive', $lazyLoad = false, $preloadImage=false)
    $img = getImageTagIfExists($relativePath, $title, "thumbsJPG{$id}", '', 'thumbsJPG img img-responsive', false, $preloadImage) . PHP_EOL;
    if (empty($doNotUseAnimatedGif) && !empty($relativePathHoverAnimation) && empty($_REQUEST['noImgGif'])) {
        $img .= getImageTagIfExists($relativePathHoverAnimation, $title, "thumbsGIF{$id}", 'position: absolute; top: 0;', 'thumbsGIF img img-responsive ', true, $preloadImage) . PHP_EOL;
    }
    return '<div class="thumbsImage">' . $img . '</div>';
}


function getImageTagIfExists($relativePath, $title = '', $id = '', $style = '', $class = 'img img-responsive', $lazyLoad = false, $preloadImage = false)
{
    global $global;
    $relativePathOriginal = $relativePath;
    $relativePath = getRelativePath($relativePath);
    $file = "{$global['systemRootPath']}{$relativePath}";
    $wh = '';
    if (file_exists($file)) {
        // check if there is a thumbs
        if (!preg_match('/_thumbsV2.jpg/', $file)) {
            $thumbs = str_replace('.jpg', '_thumbsV2.jpg', $file);
            if (file_exists($thumbs)) {
                $file = $thumbs;
            }
        }
        if (get_browser_name() !== 'Safari') {
            $file = createWebPIfNotExists($file);
        }
        $url = getURL(getRelativePath($file));
        //var_dump($relativePath, $file, $url);exit;
        if(ImagesPlaceHolders::isDefaultImage($url)){
            $class .= ' ImagesPlaceHoldersDefaultImage';
        }
        if (file_exists($file)) {
            $image_info = @getimagesize($file);
            if (!empty($image_info)) {
                $wh = $image_info[3];
            }
        }
    } elseif (isValidURL($relativePathOriginal)) {
        $url = $relativePathOriginal;
    } else {
        return '<!-- invalid URL ' . $relativePathOriginal . ' -->';
    }
    if (empty($title)) {
        $title = basename($relativePath);
    }
    $title = safeString($title);
    $img = "<img style=\"{$style}\" alt=\"{$title}\" title=\"{$title}\" id=\"{$id}\" class=\"{$class}\" {$wh} ";
    if (empty($preloadImage) && $lazyLoad) {
        if (is_string($lazyLoad)) {
            $loading = getURL($lazyLoad);
        } else {
            $loading = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
        }
        $img .= " src=\"{$loading}\" data-src=\"{$url}\" ";
    } else {
        $img .= " src=\"{$url}\" ";
    }
    $img .= "/>";
    if ($preloadImage) {
        $img = "<link rel=\"prefetch\" href=\"{$url}\" />" . $img;
    }
    return $img;
}

function im_resize_gif($file_src, $file_dest, $max_width, $max_height)
{
    if (class_exists('Imagick')) {
        $imagick = new Imagick($file_src);

        $format = $imagick->getImageFormat();
        if ($format == 'GIF') {
            $imagick = $imagick->coalesceImages();
            do {
                $imagick->resizeImage($max_width, $max_height, Imagick::FILTER_BOX, 1);
            } while ($imagick->nextImage());
            $imagick = $imagick->deconstructImages();
            $imagick->writeImages($file_dest, true);
        }

        $imagick->clear();
        $imagick->destroy();
    } else {
        copy($file_src, $file_dest);
    }
}

function im_resize_max_size($file_src, $file_dest, $max_width, $max_height)
{
    $fn = $file_src;

    $extension = mb_strtolower(pathinfo($file_dest, PATHINFO_EXTENSION));

    if ($extension == 'gif') {
        im_resize_gif($file_src, $file_dest, $max_width, $max_height);
        _error_log("im_resize_max_size($file_src) unlink line=".__LINE__);
        @unlink($file_src);
        return true;
    }

    $tmpFile = getTmpFile() . ".{$extension}";
    if (empty($fn)) {
        _error_log("im_resize_max_size: file name is empty, Destination: {$file_dest}", AVideoLog::$ERROR);
        return false;
    }
    if (function_exists("exif_read_data")) {
        error_log($fn);
        convertImage($fn, $tmpFile, 100);
        $exif = exif_read_data($tmpFile);
        if ($exif && isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            if ($orientation != 1) {
                $img = imagecreatefromjpeg($tmpFile);
                $deg = 0;
                switch ($orientation) {
                    case 3:
                        $deg = 180;
                        break;
                    case 6:
                        $deg = 270;
                        break;
                    case 8:
                        $deg = 90;
                        break;
                }
                if ($deg) {
                    $img = imagerotate($img, $deg, 0);
                }
                imagejpeg($img, $fn, 100);
            }
        }
    } else {
        _error_log("Make sure you install the php_mbstring and php_exif to be able to rotate images");
    }

    $size = getimagesize($fn);
    $ratio = $size[0] / $size[1]; // width/height
    if ($size[0] <= $max_width && $size[1] <= $max_height) {
        $width = $size[0];
        $height = $size[1];
    } elseif ($ratio > 1) {
        $width = $max_width;
        $height = $max_height / $ratio;
    } else {
        $width = $max_width * $ratio;
        $height = $max_height;
    }

    $src = imagecreatefromstring(file_get_contents($fn));
    $dst = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
    imagedestroy($src);
    imagejpeg($dst, $file_dest); // adjust format as needed
    imagedestroy($dst);
    _error_log("im_resize_max_size($file_src) unlink line=".__LINE__);
    @unlink($file_src);
    _error_log("im_resize_max_size($tmpFile) unlink line=".__LINE__);
    @unlink($tmpFile);
}

function detect_image_type($file_path)
{
    $image_info = @getimagesize($file_path);

    if ($image_info !== false) {
        $mime_type = $image_info['mime'];

        switch ($mime_type) {
            case 'image/jpeg':
                return IMAGETYPE_JPEG;
            case 'image/png':
                return IMAGETYPE_PNG;
            case 'image/gif':
                return IMAGETYPE_GIF;
            case 'image/bmp':
                return IMAGETYPE_BMP;
            case 'image/webp':
                return IMAGETYPE_WEBP;
            case 'image/x-icon':
                return IMAGETYPE_ICO;
            default:
                return false;
        }
    } else {
        return false;
    }
}


/**
 *
 * @param string $file_src
 * @return array get image size with cache
 */
function getimgsize($file_src)
{
    global $_getimagesize;
    if (empty($file_src) || !file_exists($file_src)) {
        return [0, 0];
    }
    if (empty($_getimagesize)) {
        $_getimagesize = [];
    }

    $name = "getimgsize_" . md5($file_src);

    if (!empty($_getimagesize[$name])) {
        $size = $_getimagesize[$name];
    } else {
        $cached = ObjectYPT::getCacheGlobal($name, 86400); //one day
        if (!empty($cached)) {
            $c = (array) $cached;
            $size = [];
            foreach ($c as $key => $value) {
                if (preg_match("/^[0-9]+$/", $key)) {
                    $key = intval($key);
                }
                $size[$key] = $value;
            }
            $_getimagesize[$name] = $size;
            return $size;
        }

        $size = @getimagesize($file_src);

        if (empty($size)) {
            $size = [1024, 768];
        }

        ObjectYPT::setCacheGlobal($name, $size);
        $_getimagesize[$name] = $size;
    }
    return $size;
}

function getImageFormat($file)
{
    $size = getimgsize($file);
    if ($size === false) {
        return false;
    }

    if (empty($size['mime']) || $size['mime'] == 'image/pjpeg') {
        $size['mime'] = 'image/jpeg';
    }
    //var_dump($file_src, $size);exit;
    $format = mb_strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
    $extension = $format;
    if (empty($format)) {
        $extension = mb_strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($extension === 'jpg') {
            $format = 'jpeg';
        } else {
            $size = getimgsize($file);
            if ($size === false) {
                return false;
            }

            if (empty($size['mime']) || $size['mime'] == 'image/pjpeg') {
                $size['mime'] = 'image/jpeg';
            }
            //var_dump($file_src, $size);exit;
            $format = mb_strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
            $extension = $format;
            if (empty($format)) {
                $format = 'jpeg';
                $extension = 'jpg';
            }
        }
    }

    return ['format' => $format, 'extension' => $extension];
}

function im_resize($file_src, $file_dest, $wd, $hd, $q = 80)
{
    if (empty($file_dest)) {
        return false;
    }

    if (preg_match('/notfound_/', $file_dest)) {
        return false;
    }

    if (!file_exists($file_src)) {
        _error_log("im_resize: Source not found: {$file_src}");
        return false;
    }
    $format = getImageFormat($file_src);
    $destformat = mb_strtolower(pathinfo($file_dest, PATHINFO_EXTENSION));
    $icfunc = "imagecreatefrom" . $format['format'];
    if (!function_exists($icfunc)) {
        _error_log("im_resize: Function does not exists: {$icfunc}");
        return false;
    }
    if (!file_exists($file_src)) {
        return false;
    }
    $imgSize = getimagesize($file_src);
    if (empty($imgSize)) {
        _error_log("im_resize: getimagesize($file_src) return false " . json_encode($imgSize));
        return false;
    }
    try {
        //var_dump($file_src, $icfunc);
        $src = $icfunc($file_src);
    } catch (Exception $exc) {
        _error_log("im_resize: ($file_src) " . $exc->getMessage());
        _error_log("im_resize: Try {$icfunc} from string");
        $src = imagecreatefromstring(file_get_contents($file_src));
        if (!$src) {
            _error_log("im_resize: fail {$icfunc} from string");
            return false;
        }
    }

    if (is_bool($src)) {
        //_error_log("im_resize error on source {$file_src} ", AVideoLog::$ERROR);
        return false;
    }

    $ws = imagesx($src);
    $hs = imagesy($src);

    if ($ws <= $hs) {
        $hd = ceil(($wd * $hs) / $ws);
    } else {
        $wd = ceil(($hd * $ws) / $hs);
    }
    if ($ws <= $wd) {
        $wd = $ws;
        $hd = $hs;
    }

    if (empty($hd)) {
        $hd = $hs;
    }
    if (empty($wd)) {
        $wd = $ws;
    }

    $wc = ($wd * $hs) / $hd;

    if ($wc <= $ws) {
        $hc = ($wc * $hd) / $wd;
    } else {
        $hc = ($ws * $hd) / $wd;
        $wc = ($wd * $hc) / $hd;
    }

    $dest = imagecreatetruecolor($wd, $hd);
    switch ($format) {
        case "png":
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
            imagefilledrectangle($dest, 0, 0, $wd, $hd, $transparent);

            break;
        case "gif":
            // integer representation of the color black (rgb: 0,0,0)
            $background = imagecolorallocate($src, 0, 0, 0);
            // removing the black from the placeholder
            imagecolortransparent($src, $background);

            break;
    }

    imagecopyresampled($dest, $src, 0, 0, ($ws - $wc) / 2, ($hs - $hc) / 2, $wd, $hd, $wc, $hc);
    $saved = false;
    if ($destformat === 'png') {
        $saved = imagepng($dest, $file_dest);
    } elseif ($destformat === 'jpg') {
        $saved = imagejpeg($dest, $file_dest, $q);
    } elseif ($destformat === 'webp') {
        $saved = imagewebp($dest, $file_dest, $q);
    } elseif ($destformat === 'gif') {
        $saved = imagegif($dest, $file_dest);
    }

    if (!$saved) {
        _error_log("im_resize: saving failed $file_src, $file_dest");
    }

    imagedestroy($dest);
    imagedestroy($src);
    @chmod($file_dest, 0666);

    return true;
}

function scaleUpAndMantainAspectRatioFinalSizes($new_w, $old_w, $new_h, $old_h)
{

    if (empty($old_h)) {
        $old_h = $new_h;
    }
    if (empty($new_h)) {
        $new_h = $old_h;
    }
    if (empty($old_w)) {
        $old_w = $new_w;
    }
    if (empty($new_w)) {
        $new_w = $old_w;
    }

    if (empty($old_h) || empty($new_h)) {
        // Return an error or handle the case accordingly
        return ['w' => 0, 'h' => 0];
    }
    $aspect_ratio_src = $old_w / $old_h;
    $aspect_ratio_new = $new_w / $new_h;

    if ($aspect_ratio_src > $aspect_ratio_new) {
        // The source image is wider than the specified dimensions
        $thumb_w = $new_w;
        $thumb_h = $old_h * ($new_w / $old_w);
    } else {
        // The source image is taller than the specified dimensions
        $thumb_w = $old_w * ($new_h / $old_h);
        $thumb_h = $new_h;
    }

    return ['w' => $thumb_w, 'h' => $thumb_h];
}

function scaleUpImage($file_src, $file_dest, $wd, $hd)
{
    if (!file_exists($file_src)) {
        return false;
    }

    $path = $file_src;
    $newWidth = $wd;
    $newHeight = $hd;
    $new_thumb_loc = $file_dest;

    $mime = getimagesize($path);

    if (empty($mime)) {
        $mime = mime_content_type($path);
        if ($mime == 'text/plain') {
            _error_log("scaleUpImage error, image in wrong format/mime type {$path} " . file_get_contents($path));
            unlink($path);
            return false;
        }
        _error_log("scaleUpImage error, undefined mime ".humanFileSize(filesize($file_src)));
        return false;
    }

    switch ($mime['mime']) {
        case 'image/png':
            $src_img = imagecreatefrompng($path);
            break;
        case 'image/jpg':
        case 'image/jpeg':
        case 'image/pjpeg':
            $src_img = imagecreatefromjpeg($path);
            break;
        case 'image/webp':
            $src_img = imagecreatefromwebp($path);
            break;
        default:
            _error_log("Unsupported image type: " . $mime['mime']);
            return false;
    }

    if (empty($src_img)) {
        _error_log("scaleUpImage error, we could not convert it [" . json_encode($mime) . "] " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        return false;
    }

    $old_x = imageSX($src_img);
    $old_y = imageSY($src_img);

    $sizes = scaleUpAndMantainAspectRatioFinalSizes($wd, $old_x, $hd, $old_y);
    /*
      if($wd!==200){
      echo "<h1>Original</h1>X={$old_x} Y={$old_y}";
      echo "<h1>Destination</h1>X={$wd} Y={$hd}";
      echo '<h1>Results</h1>';
      var_dump($sizes);exit;
      }
     *
     */
    $thumb_w = intval($sizes['w']);
    $thumb_h = intval($sizes['h']);

    $dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);

    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);

    switch ($mime['mime']) {
        case 'image/png':
            $result = imagepng($dst_img, $new_thumb_loc, 8);
            break;
        case 'image/jpg':
        case 'image/jpeg':
        case 'image/pjpeg':
            $result = imagejpeg($dst_img, $new_thumb_loc, 80);
            break;
        case 'image/webp':
            $result = imagewebp($dst_img, $new_thumb_loc, 80);
            break;
        default:
            _error_log("scaleUpImage error, unsupported mime type: " . $mime['mime']);
            return false;
    }

    imagedestroy($dst_img);
    imagedestroy($src_img);
    return $result;
}

function resize_png_image($source_file_path, $destination_file_path, $target_width, $target_height)
{
    // Check if the source file exists
    if (!file_exists($source_file_path)) {
        return false;
    }

    // Validate the target width and height
    if ($target_width <= 0 || $target_height <= 0) {
        return false;
    }

    $src_image = imagecreatefrompng($source_file_path);
    $src_width = imagesx($src_image);
    $src_height = imagesy($src_image);

    $target_image = imagecreatetruecolor($target_width, $target_height);
    imagealphablending($target_image, true);
    imagesavealpha($target_image, true);

    imagecopyresampled(
        $target_image,
        $src_image,
        0,
        0,
        0,
        0,
        $target_width,
        $target_height,
        $src_width,
        $src_height
    );

    $saved = imagepng($target_image, $destination_file_path);

    return $saved;
}

function convertImage($originalImage, $outputImage, $quality, $useExif = false)
{
    ini_set('memory_limit', '512M');
    if (!file_exists($originalImage) || empty(filesize($originalImage))) {
        return false;
    }

    $originalImage = str_replace('&quot;', '', $originalImage);
    $outputImage = str_replace('&quot;', '', $outputImage);
    make_path($outputImage);
    $imagetype = 0;

    if (!empty($useExif) && function_exists('exif_imagetype')) {
        $imagetype = exif_imagetype($originalImage);
    } else {
        $imagetype = detect_image_type($originalImage);
    }

    $ext = mb_strtolower(pathinfo($originalImage, PATHINFO_EXTENSION));
    $extOutput = mb_strtolower(pathinfo($outputImage, PATHINFO_EXTENSION));

    if ($ext == $extOutput) {
        //_error_log("convertImage: same extension $ext == $extOutput [$originalImage, $outputImage]");
        return copy($originalImage, $outputImage);
    }

    try {
        if ($imagetype == IMAGETYPE_WEBP) {
            //_error_log("convertImage: IMAGETYPE_WEBP");
            $imageTmp = imagecreatefromwebp($originalImage);
            if (!$imageTmp) {
                _error_log("convertImage: imagecreatefromwebp error $originalImage [$imagetype] $useExif");
                if (!$useExif) {
                    return convertImage($originalImage, $outputImage, $quality, true);
                }
                $supported_extensions = ['jpeg', 'png', 'bmp', 'gif'];
                foreach ($supported_extensions as $ext) {
                    $function_name = "imagecreatefrom$ext";
                    $imageTmp = @$function_name($originalImage);
                    if ($imageTmp) {
                        break;
                    } else {
                        //_error_log("convertImage: Could not create image resource using $function_name");
                    }
                }
                if (!$imageTmp) {
                    copy($originalImage, $outputImage);
                    _error_log("convertImage: Could not create image resource for $originalImage we will just copy it");
                    return false;
                }
            }
        }
        if (empty($imageTmp)) {
            if ($imagetype === IMAGETYPE_JPEG || preg_match('/jpg|jpeg/i', $ext)) {
                //_error_log("convertImage: IMAGETYPE_JPEG");
                $imageTmp = imagecreatefromjpeg($originalImage);
            } elseif ($imagetype == IMAGETYPE_PNG || preg_match('/png/i', $ext)) {
                //_error_log("convertImage: IMAGETYPE_PNG");
                $imageTmp = imagecreatefrompng($originalImage);
            } elseif ($imagetype == IMAGETYPE_GIF || preg_match('/gif/i', $ext)) {
                //_error_log("convertImage: IMAGETYPE_GIF");
                $imageTmp = imagecreatefromgif($originalImage);
            } elseif ($imagetype == IMAGETYPE_BMP || preg_match('/bmp/i', $ext)) {
                //_error_log("convertImage: IMAGETYPE_BMP");
                $imageTmp = imagecreatefrombmp($originalImage);
            } elseif ($imagetype == IMAGETYPE_WEBP || preg_match('/webp/i', $ext)) {
                //_error_log("convertImage: IMAGETYPE_WEBP");
                $imageTmp = imagecreatefromwebp($originalImage);
            } else {
                _error_log("convertImage: File Extension not found ($originalImage, $outputImage, $quality) " . exif_imagetype($originalImage));
                return 0;
            }
        }
    } catch (Exception $exc) {
        _error_log("convertImage: " . $exc->getMessage());
        return 0;
    }
    if ($imageTmp === false) {
        //_error_log("convertImage: could not create a resource: [$imagetype] $originalImage, $outputImage, $quality, $ext ");
        return 0;
    }
    // quality is a value from 0 (worst) to 100 (best)
    $response = 0;
    if ($extOutput === 'jpg') {
        if (function_exists('imagejpeg')) {
            $response = imagejpeg($imageTmp, $outputImage, $quality);
        } else {
            _error_log("convertImage ERROR: function imagejpeg does not exists");
        }
    } elseif ($extOutput === 'png') {
        if (function_exists('imagepng')) {
            $quality = max(-1, min(9, (int) round($quality / 10)));
            $response = imagepng($imageTmp, $outputImage, $quality);
        } else {
            _error_log("convertImage ERROR: function imagepng does not exists");
        }
    } elseif ($extOutput === 'webp') {
        if (function_exists('imagewebp')) {
            $response = imagewebp($imageTmp, $outputImage, $quality);
        } else {
            _error_log("convertImage ERROR: function imagewebp does not exists");
        }
    } elseif ($extOutput === 'gif') {
        if (function_exists('imagegif')) {
            $response = imagegif($imageTmp, $outputImage);
        } else {
            _error_log("convertImage ERROR: function imagegif does not exists");
        }
    }

    imagedestroy($imageTmp);

    return $response;
}

function base64DataToImage($imgBase64)
{
    $img = $imgBase64;
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    return base64_decode($img);
}

function saveBase64DataToPNGImage($imgBase64, $filePath)
{
    $fileData = base64DataToImage($imgBase64);
    if (empty($fileData)) {
        return false;
    }
    return _file_put_contents($filePath, $fileData);
}


function createWebPIfNotExists($path)
{
    if (version_compare(PHP_VERSION, '8.0.0') < 0 || !file_exists($path)) {
        return $path;
    }
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    if ($extension !== 'jpg') {
        return $path;
    }
    $nextGenPath = str_replace('.jpg', '_jpg.webp', $path);

    if (!file_exists($nextGenPath)) {
        convertImage($path, $nextGenPath, 90);
    }
    return $nextGenPath;
}


function getCroppie(
    $buttonTitle,
    $callBackJSFunction,
    $resultWidth = 0,
    $resultHeight = 0,
    $viewportWidth = 0,
    $boundary = 25,
    $viewportHeight = 0,
    $enforceBoundary = true
) {
    global $global;
    require_once $global['systemRootPath'] . 'objects/functionCroppie.php';
    return getCroppieElement(
        $buttonTitle,
        $callBackJSFunction,
        $resultWidth ,
        $resultHeight ,
        $viewportWidth,
        $boundary ,
        $viewportHeight,
        $enforceBoundary );
}

function saveCroppieImage($destination, $postIndex = "imgBase64")
{
    global $global;
    require_once $global['systemRootPath'] . 'objects/functionCroppie.php';
    return saveCroppieImageElement($destination, $postIndex);
}


function isImageNotFound($imgURL){
    if(empty($imgURL)){
        return true;
    }

    return ImagesPlaceHolders::isDefaultImage($imgURL);
}

function createColorfulTextSpans($string)
{
    $output = '<span class="colorful-text">';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = htmlspecialchars($string[$i]);
        $output .= "<span>$char</span>";
    }
    $output .= '</span>';
    return $output;
}

<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class ImageGallery extends PluginAbstract
{
    public function getTags()
    {
        return array(
            PluginTags::$FREE
        );
    }

    public function getDescription()
    {
        global $global;
        $str = "Create ImageGallery";
        return $str;
    }

    public function getName()
    {
        return "ImageGallery";
    }

    public function getUUID()
    {
        return "ImageGallery-91db-4357-bb10-ee08b0913778";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        return $obj;
    }

    static function isInvalidType($videos_id)
    {
        if (empty($videos_id)) {
            return true;
        }
        $video = new Video('', '', $videos_id);

        if ($video->getType() != Video::$videoTypeImage && $video->getType() != Video::$videoTypeGallery) {           
            return true;
        }
        return false;
    }

    static function dieIfIsInvalid($videos_id)
    {
        if (self::isInvalidType($videos_id)) {
            $video = new Video('', '', $videos_id);
            forbiddenPage('This media is not an image, type=['.$video->getType().']');
        }
    }

    private static function getRelative($videos_id)
    {        
        ImageGallery::dieIfIsInvalid($videos_id);
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        $dir = "videos/{$filename}/ImageGallery/";
        return $dir;
    }

    static function getImageDir($videos_id)
    {
        global $global;
        $relative = self::getRelative($videos_id);

        $dir = "{$global['systemRootPath']}{$relative}";

        make_path($dir);

        return $dir;
    }

    static function saveFile($file, $videos_id)
    {
        // Define allowed MIME types
        $allowedMimeTypes = ['image/jpeg', 'image/webp', 'image/gif', 'image/png', 'video/mp4'];

        $directory = self::getImageDir($videos_id);

        // Check MIME type of the file
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($file['tmp_name']);

        if (in_array($fileType, $allowedMimeTypes)) {
            // Generate unique filename to avoid overwriting
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            do {
                $newFilename = uniqid() . '.' . $extension;
                $newFilePath = $directory . $newFilename;
            } while (file_exists($newFilePath));

            // Move the file to the target directory
            if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    static function listFiles($videos_id)
    {
        $filesList = [];
        $directory = self::getImageDir($videos_id);
        $relative = self::getRelative($videos_id);

        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isFile()) {
                $fileBase = $file->getBasename();
                $filePath = $file->getPathname();
                $fileType = mime_content_type($filePath);
                $relativeBaseName = "{$relative}{$fileBase}";
                $filesList[] = [
                    //'path' => $filePath, 
                    'base' => $fileBase, 
                    'type' => $fileType, 
                    'url'=>getURL($relativeBaseName)
                ];
            }
        }

        return $filesList;
    }

    static function deleteFile($filename, $videos_id)
    {
        // Securely prevent path traversal
        $directory = self::getImageDir($videos_id);
        $filename = basename($filename);
        $filename = preg_replace('/[^a-z0-9.-]/i', '', $filename);
        $filePath = $directory . '/' . $filename;

        // Check if file exists and delete
        if (file_exists($filePath)) {
            return unlink($filePath);
        } else {
            return false;
        }
    }

    
    public static function getManagerVideosTab(){
        return '<li id="pImageGalleryLI" class="showIfIsImage"><a data-toggle="tab" href="#pImageGallery"><i class="fa-regular fa-images"></i> ' . __("Image Gallery") . '</a></li>';
    }

    public static function getManagerVideosBody(){
        return '<div id="pImageGallery" class="tab-pane fade showIfIsImage"><iframe  style="width: 100%; height: 65vh;" src="about:blank" allowfullscreen></iframe></div>';
    }

    public static function getManagerVideosEdit() {
        $js = "$('#pImageGallery iframe').attr('src', webSiteRootURL + 'plugin/ImageGallery/upload.php?avideoIframe=1&videos_id=' + row.id);";
        return $js;
    }
}
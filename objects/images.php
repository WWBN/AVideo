<?php

class ImagesPlaceHolders
{

    public static $RETURN_RELATIVE = 0;
    public static $RETURN_PATH = 1;
    public static $RETURN_URL = 2;
    public static $RETURN_ARRAY = 3;
    static $placeholders = [
        'animationLandscape' => 'view/img/placeholders/animationLandscape.webp',
        'animationPortrait' => 'view/img/placeholders/animationPortrait.webp',
        'imageIcon' => 'view/img/placeholders/image.png',
        'imageLandscape' => 'view/img/placeholders/imageLandscape.png',
        'imagePortrait' => 'view/img/placeholders/imagePortrait.png',
        'imageNotFoundIcon' => 'view/img/placeholders/imageNotFound.png',
        'imageNotFoundLandscape' => 'view/img/placeholders/imageNotFoundLandscape.png',
        'imageNotFoundPortrait' => 'view/img/placeholders/imageNotFoundPortrait.png',
        'articlesIcon' => 'view/img/placeholders/articles.png',
        'articlesLandscape' => 'view/img/placeholders/articlesLandscape.png',
        'articlesPortrait' => 'view/img/placeholders/articlesPortrait.png',
        'audioIcon' => 'view/img/placeholders/audio.png',
        'audioLandscape' => 'view/img/placeholders/audioLandscape.png',
        'audioPortrait' => 'view/img/placeholders/audioPortrait.png',
        'pdfIcon' => 'view/img/placeholders/pdf.png',
        'pdfLandscape' => 'view/img/placeholders/pdfLandscape.png',
        'pdfPortrait' => 'view/img/placeholders/pdfPortrait.png',
        'userIcon' => 'view/img/placeholders/user.png',
        'userLandscape' => 'view/img/placeholders/userLandscape.png',
        'userPortrait' => 'view/img/placeholders/userPortrait.png',
        'zipIcon' => 'view/img/placeholders/zip.png',
        'zipLandscape' => 'view/img/placeholders/zipLandscape.png',
        'zipPortrait' => 'view/img/placeholders/zipPortrait.png',
        'videoPlaceholder' => 'view/img/video-placeholder-gray.png',
        'videoPlaceholderPortrait' => 'view/img/video-placeholder-gray-portrait.png',
        'videoNotFoundPoster' => 'view/img/videoNotFound.png',
    ];

    static function getComponent($type, $return = 0)
    {
        global $global;
        if (!isset(self::$placeholders[$type])) {
            return null; // handle invalid type
        }
        $relativePath = self::$placeholders[$type];

        if (self::supportsWebP()) {
            $relativePathWebp = str_replace(array('.jpg', '.png'), array('.webp', '.webp'), $relativePath);
            if (file_exists($global['systemRootPath'] . $relativePathWebp)) {
                $relativePath = $relativePathWebp;
            }
        }

        if (!empty($global[$type])) {
            $relativePath = $global[$type];
        }
        if ($return === ImagesPlaceHolders::$RETURN_URL) {
            return getURL($relativePath);
        }
        if ($return === ImagesPlaceHolders::$RETURN_PATH) {
            return $global['systemRootPath'] . $relativePath;
        }
        if ($return === ImagesPlaceHolders::$RETURN_ARRAY) {
            $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
            return [
                'filename' => basename($relativePath),
                'path' => $global['systemRootPath'] . $relativePath,
                'url' => getURL($relativePath),
                'url_noCDN' => $global['webSiteRootURL'] . $relativePath,
                'type' => 'image',
                'format' => $extension,
            ];
        }
        return $relativePath;
    }

    static function supportsWebP()
    {
        static $supportsWebP = null;

        // If we've already tested this before, return the cached value
        if ($supportsWebP !== null) {
            return $supportsWebP;
        }

        // First, check the HTTP_ACCEPT value
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
            $supportsWebP = true;
            return $supportsWebP;
        }

        // If HTTP_ACCEPT is inconclusive, check User-Agent as a fallback
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];

            if (preg_match('/Firefox\/(\d+)/', $ua, $matches) && intval($matches[1]) >= 65) {
                $supportsWebP = true;
                return $supportsWebP;
            }

            if (preg_match('/Opera\/.*Version\/(\d+)/', $ua, $matches) && intval($matches[1]) >= 56) {
                $supportsWebP = true;
                return $supportsWebP;
            }

            $webpBrowsersPatterns = [
                '/Chrome\//',                  // Chrome (and most Chromium-based browsers)
                '/Edge\//',                    // Edge
                '/UCBrowser\//',               // UC Browser
                '/SamsungBrowser/'             // Samsung Internet Browser
            ];

            foreach ($webpBrowsersPatterns as $pattern) {
                if (preg_match($pattern, $ua)) {
                    $supportsWebP = true;
                    return $supportsWebP;
                }
            }
        }

        // Default to false if no evidence found
        $supportsWebP = false;
        return $supportsWebP;
    }


    static function isDefaultImage($path)
    {
        global $global;

        foreach (self::$placeholders as $key => $defaultImagePath) {
            if (strpos($path, $defaultImagePath) !== false) {
                //var_dump("isDefaultImage 1: {$defaultImagePath} === {$path}");
                return true;
            }
            $defaultImage = self::getComponent($key);
            if (strpos($path, $defaultImage) !== false) {
                //var_dump("isDefaultImage 2: {$defaultImage} === {$path}");
                return true;
            }
            $basename = basename($path);
            if (strpos($defaultImage, $basename) !== false) {
                //var_dump("isDefaultImage: {$defaultImage} contains {$basename}");
                return true;
            }
            $fullPath = $global['systemRootPath'] . $defaultImage;
            $urlPath = getURL($defaultImage);

            if ($path === $defaultImage || $path === $fullPath || $path === $urlPath) {
                //var_dump("isDefaultImage: {$path} === {$defaultImage} || {$fullPath} || {$urlPath}");
                return true;
            }

            //var_dump("isDefaultImage: {$path} does not match $defaultImagePath || {$defaultImage} || {$fullPath} || {$urlPath}");
        }

        return false;
    }

    static function getImageIcon($return = 0)
    {
        return self::getComponent('imageIcon', $return);
    }

    static function getImageLandscape($return = 0)
    {
        return self::getComponent('imageLandscape', $return);
    }

    static function getImagePortrait($return = 0)
    {
        return self::getComponent('imagePortrait', $return);
    }

    static function getImageNotFoundIcon($return = 0)
    {
        return self::getComponent('imageNotFoundIcon', $return);
    }

    static function getImageNotFoundLandscape($return = 0)
    {
        return self::getComponent('imageNotFoundLandscape', $return);
    }

    static function getImageNotFoundPortrait($return = 0)
    {
        return self::getComponent('imageNotFoundPortrait', $return);
    }

    static function getArticlesIcon($return = 0)
    {
        return self::getComponent('articlesIcon', $return);
    }

    static function getArticlesLandscape($return = 0)
    {
        return self::getComponent('articlesLandscape', $return);
    }

    static function getArticlesPortrait($return = 0)
    {
        return self::getComponent('articlesPortrait', $return);
    }

    static function getAudioIcon($return = 0)
    {
        return self::getComponent('audioIcon', $return);
    }

    static function getAudioLandscape($return = 0)
    {
        return self::getComponent('audioLandscape', $return);
    }

    static function getAudioPortrait($return = 0)
    {
        return self::getComponent('audioPortrait', $return);
    }

    static function getPdfIcon($return = 0)
    {
        return self::getComponent('pdfIcon', $return);
    }

    static function getPdfLandscape($return = 0)
    {
        return self::getComponent('pdfLandscape', $return);
    }

    static function getPdfPortrait($return = 0)
    {
        return self::getComponent('pdfPortrait', $return);
    }

    static function getUserIcon($return = 0)
    {
        return self::getComponent('userIcon', $return);
    }

    static function getUserLandscape($return = 0)
    {
        return self::getComponent('userLandscape', $return);
    }

    static function getUserPortrait($return = 0)
    {
        return self::getComponent('userPortrait', $return);
    }

    static function getZipIcon($return = 0)
    {
        return self::getComponent('zipIcon', $return);
    }

    static function getZipLandscape($return = 0)
    {
        return self::getComponent('zipLandscape', $return);
    }

    static function getZipPortrait($return = 0)
    {
        return self::getComponent('zipPortrait', $return);
    }

    static function getVideoPlaceholder($return = 0)
    {
        return self::getComponent('videoPlaceholder', $return);
    }
    static function getVideoPlaceholderPortrait($return = 0)
    {
        return self::getComponent('videoPlaceholderPortrait', $return);
    }
    static function getVideoNotFoundPoster($return = 0)
    {
        return self::getComponent('videoNotFoundPoster', $return);
    }
    static function getVideoAnimationLandscape($return = 0)
    {
        return self::getComponent('animationLandscape', $return);
    }
    static function getVideoAnimationPortrait($return = 0)
    {
        return self::getComponent('animationPortrait', $return);
    }
}

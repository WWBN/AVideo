<?php

Class ImagesPlaceHolders {

    public static $RETURN_RELATIVE = 0;
    public static $RETURN_PATH = 1;
    public static $RETURN_URL = 2;
    public static $RETURN_ARRAY = 3;
    private static $placeholders = [
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
        'videoNotFoundPoster' => 'view/img/this-video-is-not-available.jpg'
    ];

    static private function getComponent($type, $return = 0) {
        global $global;
        if (!isset(self::$placeholders[$type])) {
            return null; // handle invalid type
        }
        $relativePath = self::$placeholders[$type];
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
                'filename' =>basename($relativePath),
                'path' => $global['systemRootPath'] . $relativePath,
                'url' => getURL($relativePath),
                'url_noCDN' => $global['webSiteRootURL'] . $relativePath,
                'type' => 'image',
                'format' => $extension,
            ];
        }
        return $relativePath;
    }

    static function isDefaultImage($path) {
        global $global;

        foreach (self::$placeholders as $key => $defaultImagePath) {
            $defaultImage = self::getComponent($key);
            if (strpos($defaultImage, $path) !== false) {
                return true;
            }
            $basename = basename($path);
            if (strpos($defaultImage, $basename) !== false) {
                return true;
            }
            $fullPath = $global['systemRootPath'] . $defaultImage;
            $urlPath = getURL($defaultImage);

            if ($path === $defaultImage || $path === $fullPath || $path === $urlPath) {
                return true;
            }
        }

        return false;
    }

    static function getImageIcon($return = 0) {
        return self::getComponent('imageIcon', $return);
    }

    static function getImageLandscape($return = 0) {
        return self::getComponent('imageLandscape', $return);
    }

    static function getImagePortrait($return = 0) {
        return self::getComponent('imagePortrait', $return);
    }

    static function getImageNotFoundIcon($return = 0) {
        return self::getComponent('imageNotFoundIcon', $return);
    }

    static function getImageNotFoundLandscape($return = 0) {
        return self::getComponent('imageNotFoundLandscape', $return);
    }

    static function getImageNotFoundPortrait($return = 0) {
        return self::getComponent('imageNotFoundPortrait', $return);
    }

    static function getArticlesIcon($return = 0) {
        return self::getComponent('articlesIcon', $return);
    }

    static function getArticlesLandscape($return = 0) {
        return self::getComponent('articlesLandscape', $return);
    }

    static function getArticlesPortrait($return = 0) {
        return self::getComponent('articlesPortrait', $return);
    }

    static function getAudioIcon($return = 0) {
        return self::getComponent('audioIcon', $return);
    }

    static function getAudioLandscape($return = 0) {
        return self::getComponent('audioLandscape', $return);
    }

    static function getAudioPortrait($return = 0) {
        return self::getComponent('audioPortrait', $return);
    }

    static function getPdfIcon($return = 0) {
        return self::getComponent('pdfIcon', $return);
    }

    static function getPdfLandscape($return = 0) {
        return self::getComponent('pdfLandscape', $return);
    }

    static function getPdfPortrait($return = 0) {
        return self::getComponent('pdfPortrait', $return);
    }

    static function getUserIcon($return = 0) {
        return self::getComponent('userIcon', $return);
    }

    static function getUserLandscape($return = 0) {
        return self::getComponent('userLandscape', $return);
    }

    static function getUserPortrait($return = 0) {
        return self::getComponent('userPortrait', $return);
    }

    static function getZipIcon($return = 0) {
        return self::getComponent('zipIcon', $return);
    }

    static function getZipLandscape($return = 0) {
        return self::getComponent('zipLandscape', $return);
    }

    static function getZipPortrait($return = 0) {
        return self::getComponent('zipPortrait', $return);
    }

    static function getVideoPlaceholder($return = 0) {
        return self::getComponent('videoPlaceholder', $return);
    }
    static function getVideoPlaceholderPortrait($return = 0) {
        return self::getComponent('videoPlaceholderPortrait', $return);
    }
    static function getVideoNotFoundPoster($return = 0) {
        return self::getComponent('videoNotFoundPoster', $return);
    }

}

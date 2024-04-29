<?php

class SocialUploader
{
    public static function upload($publisher_user_preferences_id, $videoPath, $title, $description, $isShort = false)
    {
        $accessToken = SocialMediaPublisher::getRevalidatedToken($publisher_user_preferences_id, true);
        if (empty($accessToken)) {
            var_dump($publisher_user_preferences_id, $videoPath, $title, $description, $isShort, debug_backtrace());
            die('Empty access token');
        }
        $pub = new Publisher_user_preferences($publisher_user_preferences_id);
        switch ($pub->getProviderName()) {
            case SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE['name']:
                return SocialUploader::uploadYouTube($accessToken, $videoPath, $title, $description, $isShort);
                break;
            case SocialMediaPublisher::SOCIAL_TYPE_FACEBOOK['name']:
                $json = json_decode($pub->getJson());
                $pageId = $json->{'restream.ypt.me'}->facebook->broadcaster_id;
                return SocialUploader::uploadFacebook($accessToken, $pageId, $videoPath, $title, $description, $isShort);
                break;
            case SocialMediaPublisher::SOCIAL_TYPE_INSTAGRAM['name']:
                //return SocialUploader::uploadYouTube($accessToken, $videoPath, $title, $description, $isShort);
                break;
            case SocialMediaPublisher::SOCIAL_TYPE_TWITCH['name']:
                //return SocialUploader::uploadYouTube($accessToken, $videoPath, $title, $description, $isShort);
                break;
            case SocialMediaPublisher::SOCIAL_TYPE_LINKEDIN['name']:
                return SocialUploader::uploadLinkedIn($accessToken, $videoPath, $title, $description, $isShort);
                break;
        }
        return false;
    }

    private static function uploadLinkedIn($accessToken, $videoPath, $title, $description, $isShort = false)
    {
        return LinkedInUploader::upload($accessToken, $ownerId, $videoPath, $title, $description);
    }
    private static function uploadYouTube($accessToken, $videoPath, $title, $description, $isShort = false)
    {
        //var_dump($accessToken, $videoPath, filesize($videoPath), file_exists($videoPath));exit;
        $client = new Google_Client();
        $client->setAccessToken($accessToken); // Ensure this token is valid

        $youtube = new Google_Service_YouTube($client);

        $video = new Google_Service_YouTube_Video();
        $video->setSnippet(new Google_Service_YouTube_VideoSnippet());
        $video->getSnippet()->setTitle($title);
        if ($isShort) {
            $video->getSnippet()->setDescription($description . " #Shorts"); // Mark as a YouTube Short in the description
        } else {
            $video->getSnippet()->setDescription($description);
        }
        //$video->getSnippet()->setTags(['tag1', 'tag2']);
        //$video->getSnippet()->setCategoryId('22'); // For example, 'People & Blogs'
        $video->setStatus(new Google_Service_YouTube_VideoStatus());
        $video->getStatus()->setPrivacyStatus('public');

        $chunkSizeBytes = 1 * 1024 * 1024; // Define the size of each chunk
        $client->setDefer(true);

        $insertRequest = $youtube->videos->insert("status,snippet", $video);

        $media = new Google_Http_MediaFileUpload(
            $client,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($videoPath));

        $status = false;
        $handle = fopen($videoPath, "rb");
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }
        fclose($handle);

        $client->setDefer(false);
        return $status;
    }

    private static function uploadFacebook($accessToken, $pageId, $videoPath, $title, $description, $isShort = false)
    {
        if ($isShort) {
            return FacebookUploader::uploadFacebookReels($accessToken, $pageId, $videoPath, $title, $description);
        } else {
            return FacebookUploader::uploadFacebookVideo($accessToken, $pageId, $videoPath, $title, $description);
        }
    }
}


class FacebookUploader
{
    static function uploadFacebookVideo($accessToken, $pageId, $videoPath, $title, $description)
    {
        global  $global;
        // Upload a local video directly
        $videoUrl = str_replace(getVideosDir(), $global['webSiteRootURL'], $videoPath);
        $VideoUploadResponse = FacebookUploader::uploadDirectHostedVideo($pageId, $accessToken, $videoUrl);
        //$VideoUploadResponse = FacebookUploader::uploadDirectLocalVideo($pageId, $accessToken, $videoPath);

        $return = [
            'error' => true,
            'msg' => '',
            'line' => array(__LINE__)
        ];
        $return['videoUrl'] = $videoUrl;
        $return['VideoUploadResponse'] = $VideoUploadResponse;

        if (isset($VideoUploadResponse['success']) && $VideoUploadResponse['success']) {
            $return['line'][] = __LINE__;
            $return['error'] = false;
            return $return;
        } else {
            $return['line'][] = __LINE__;
            $return['msg'] = isset($VideoUploadResponse['error']) ? $VideoUploadResponse['error'] : 'Unknown error occurred during video upload.';
            return $return;
        }
    }

    static function uploadFacebookReels($accessToken, $pageId, $videoPath, $title, $description)
    {
        $return = [
            'error' => true,
            'msg' => '',
            'line' => array(__LINE__)
        ];
        // Initialize upload session
        $initResponse = FacebookUploader::initializeFacebookUploadSession($pageId, $accessToken);
        $return['initResponse'] = $initResponse;
        if (isset($initResponse['video_id']) && isset($initResponse['upload_url'])) {
            $videoId = $initResponse['video_id'];
            $uploadResponse = FacebookUploader::uploadLocalVideoToFacebook($videoId, $accessToken, $videoPath);
            $return['uploadResponse'] = $uploadResponse;

            if (isset($uploadResponse['success']) && $uploadResponse['success']) {
                $return['line'][] = __LINE__;
                $return['error'] = false;
                return $return;
            } else {
                $return['line'][] = __LINE__;
                $return['msg'] = isset($uploadResponse['error']) ? $uploadResponse['error'] : 'Failed to upload video.';
                return $return;
            }
        } else {
            $return['line'][] = __LINE__;
            $return['msg'] = isset($initResponse['error']) ? $initResponse['error'] : 'Failed to initialize upload session.';
            return $return;
        }
    }
    static function initializeFacebookUploadSession($pageId, $accessToken)
    {
        $url = "https://graph.facebook.com/v19.0/{$pageId}/video_reels";
        $data = json_encode([
            "upload_phase" => "start",
            "access_token" => $accessToken
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    static function uploadLocalVideoToFacebook($videoId, $accessToken, $filePath)
    {
        $url = "https://rupload.facebook.com/video-upload/v19.0/{$videoId}";
        $fileSize = filesize($filePath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: OAuth {$accessToken}",
            "offset: 0",
            "file_size: {$fileSize}",
            'Content-Type: application/octet-stream'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    static function uploadHostedVideoToFacebook($videoId, $accessToken, $fileUrl)
    {
        $url = "https://rupload.facebook.com/video-upload/v19.0/{$videoId}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: OAuth {$accessToken}",
            "file_url: {$fileUrl}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    static function uploadDirectLocalVideo($pageId, $accessToken, $filePath)
    {
        $fileSize = filesize($filePath);
        if ($fileSize > 1 * 1024 * 1024 * 1024) {
            // Handle error if file is larger than 1GB
            return json_encode(['error' => 'File size exceeds 1 GB limit']);
        }

        $url = "https://graph-video.facebook.com/v19.0/{$pageId}/videos";
        $postData = [
            'access_token' => $accessToken,
            'source' => new CURLFile($filePath),
        ];

        $ch = curl_init();
        /*
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}"
        ]);*/
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        _error_log("uploadDirectLocalVideo($pageId, $accessToken, $filePath) $response");
        if (curl_errno($ch)) {
            return json_encode(['error' => curl_error($ch)]);
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    static function uploadDirectHostedVideo($pageId, $accessToken, $videoUrl)
    {
        $url = "https://graph-video.facebook.com/v19.0/{$pageId}/videos";
        $postData = [
            'access_token' => $accessToken,
            'file_url' => $videoUrl
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}"
        ]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return json_encode(['error' => curl_error($ch)]);
        }
        curl_close($ch);

        return json_decode($response, true);
    }
}

class LinkedInUploader
{


    static function upload($accessToken, $ownerId, $videoPath, $title, $description)
    {
        $return = [
            'error' => true,
            'msg' => '',
            'initResponse' => null,
            'uploadResponse' => null
        ];

        // Initialize upload session
        $initResponse = LinkedInUploader::initializeLinkedInUploadSession($accessToken, $ownerId);
        $return['initResponse'] = $initResponse;

        // Check if the initialization was successful
        if (isset($initResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'])) {
            $uploadUrl = $initResponse['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];

            // Upload the video
            $uploadResponse = LinkedInUploader::uploadVideoToLinkedIn($uploadUrl, $accessToken, $videoPath);
            $return['uploadResponse'] = $uploadResponse;

            // Check if the upload was successful
            if (isset($uploadResponse['error']) && $uploadResponse['error']) {
                $return['msg'] = "Error uploading video: " . $uploadResponse['message'];
            } else {
                $return['error'] = false;
                $return['msg'] = "Video uploaded successfully!";
            }
        } else {
            $return['msg'] = "Failed to initialize upload session: " . json_encode($initResponse);
        }

        return $return;
    }

    static function initializeLinkedInUploadSession($accessToken, $ownerId)
    {
        $url = "https://api.linkedin.com/v2/assets?action=registerUpload";
        $data = json_encode([
            "registerUploadRequest" => [
                "recipes" => [
                    "urn:li:digitalmediaRecipe:feedshare-video"
                ],
                "owner" => 'urn:li:person:' . $ownerId,
                "serviceRelationships" => [
                    [
                        "relationshipType" => "OWNER",
                        "identifier" => "urn:li:userGeneratedContent"
                    ]
                ]
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
            "X-RestLi-Protocol-Version: 2.0.0"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return ['error' => true, 'message' => curl_error($ch)];
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    static function uploadVideoToLinkedIn($uploadUrl, $accessToken, $filePath)
    {
        $fileSize = filesize($filePath);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/octet-stream",
            "Content-Length: {$fileSize}"
        ]);
        curl_setopt($ch, CURLOPT_PUT, true);

        $fp = fopen($filePath, 'rb');
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            fclose($fp);
            return ['error' => true, 'message' => curl_error($ch)];
        }

        curl_close($ch);
        fclose($fp);

        return json_decode($response, true);
    }
}

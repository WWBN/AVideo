<?php

class SocialUploader
{
    public static function upload($publisher_user_preferences_id, $videoPath, $title, $description, $isShort = false)
    {
        $title = strip_tags($title);
        $description = strip_tags($description);
        $accessToken = SocialMediaPublisher::getRevalidatedToken($publisher_user_preferences_id, true);
        if (empty($accessToken)) {
            return array('error' => true, 'msg' => 'Empty access token', 'line' => __LINE__);
        }
        $pub = new Publisher_user_preferences($publisher_user_preferences_id);
        try {
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
                    $json = json_decode($pub->getJson());
                    $urn = $json->{'restream.ypt.me'}->linkedin->urn;
                    $id = $json->{'restream.ypt.me'}->linkedin->profile_id;
                    return SocialUploader::uploadLinkedIn($accessToken, $urn, $id, $videoPath, $title, $description, $isShort);
                    break;
            }
        } catch (\Throwable $th) {
            $error = $th->getMessage();
            if (is_string($error)) {
                $obj = json_decode($error);
                if (!empty($obj->error)) {
                    if (!empty($obj->error->message)) {
                        return array('error' => true, 'msg' => $obj->error->message, 'line' => __LINE__);
                    } else {
                        return array('error' => true, 'msg' => $error, 'line' => __LINE__);
                    }
                }
            } else {
                return array('error' => true, 'msg' => $error, 'line' => __LINE__);
            }
        }
        return false;
    }

    private static function uploadLinkedIn($accessToken, $urn, $id, $videoPath, $title, $description, $isShort = false)
    {
        return LinkedInUploader::upload($accessToken, $urn, $id , $videoPath, $title, $description);
    }
    private static function uploadYouTube($accessToken, $videoPath, $title, $description, $isShort = false)
    {
        $title = _substr($title, 0, 90);
        $description = _substr($description, 0, 4000);

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


    static public function getErrorMsg($obj)
    {
        $line = __LINE__;
        $msg = '';
        $obj = object_to_array($obj);
        if (!empty($obj["msg"]) && is_string($obj["msg"])) {
            $line = __LINE__;
            $msg = $obj["msg"];
        } else if (!empty($obj["msg"]["message"])) {
            $line = __LINE__;
            $msg = $obj["msg"]["message"];
        } else if (!empty($obj["error"]["msg"])) {
            $line = __LINE__;
            $msg = $obj["error"]["msg"];
        } else if (!empty($obj["message"])) {
            $line = __LINE__;
            $msg = $obj["message"];
        }
        if (!is_string($msg)) {
            var_dump($line, $msg, $obj);
            exit;
        }
        return strip_tags($msg);
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
            $return['msg'] = isset($initResponse['error']) ? $initResponse['error'] : 'Failed to initialize FB upload session.';
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
    //version number in the format YYYYMM
    const versionNumber = '202404';

    static function upload($accessToken, $urn, $id, $videoPath, $title, $description)
    {
        $return = [
            'error' => true,
            'msg' => '',
            'initResponse' => null,
            'uploadResponse' => null
        ];

        $fileSizeBytes = filesize($videoPath);

        // Initialize upload session
        $initResponse = LinkedInUploader::initializeLinkedInUploadSession($accessToken, $urn, $fileSizeBytes);
        $return['initResponse'] = $initResponse;
        // Check if the initialization was successful
        if (isset($initResponse) && isset($initResponse['value']) && !empty($initResponse['value']['uploadInstructions'])) {
            $return['uploadResponse'] = array();
            $return['msg'] = array();

            foreach ($initResponse['value']['uploadInstructions'] as $key => $value) {
                $uploadResponse = LinkedInUploader::uploadVideoToLinkedIn($value['uploadUrl'], $accessToken, $videoPath, $value['firstByte'], $value['lastByte']);
                $return['uploadResponse'][] = $uploadResponse;
                if (isset($uploadResponse['error']) && $uploadResponse['error']) {
                    $return['msg'][] = "Error uploading video httpcode:" . $uploadResponse['httpcode'];
                }
            }
        } else {
            $return['msg'] = "Failed to initialize upload session";
        }

        return $return;
    }

    static function initializeLinkedInUploadSession($accessToken, $urn, $fileSizeBytes)
    {
        $url = "https://api.linkedin.com/rest/videos?action=initializeUpload";
        $data = json_encode([
            "initializeUploadRequest" => [
                "owner" => $urn,
                "fileSizeBytes" => $fileSizeBytes,
                "uploadCaptions" => false,
                "uploadThumbnail" => false
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$accessToken}",
            "X-RestLi-Protocol-Version: 2.0.0",
            "LinkedIn-Version: " . LinkedInUploader::versionNumber
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

    static function uploadVideoToLinkedIn($uploadUrl, $accessToken, $filePath, $firstByte, $lastByte)
    {
        $fileSize = $lastByte - $firstByte + 1; // Calculate the size of the portion to upload
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            //"Authorization: Bearer {$accessToken}",
            "Content-Type: application/octet-stream",
            "Content-Length: {$fileSize}",
            //"X-RestLi-Protocol-Version: 2.0.0",
            //"LinkedIn-Version: " . LinkedInUploader::versionNumber
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Use CURLOPT_CUSTOMREQUEST "PUT"
    
        $fp = fopen($filePath, 'rb');
        // Move the file pointer to the starting byte
        fseek($fp, $firstByte);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);  // Enable header response
    
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if (curl_errno($ch)) {
            fclose($fp);
            curl_close($ch);
            return ['error' => true, 'msg' => curl_error($ch), 'json' => false];
        }
    
        $header_size = $info['header_size'];
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
    
        $etag = null;
        if (preg_match('/etag: ([^\r\n]+)/i', $header, $matches)) {
            $etag = trim($matches[1]);
        }
    
        fclose($fp);
        curl_close($ch);
    
        return [
            'uploadUrl' => $uploadUrl,
            'error' => empty($etag),
            'msg' => empty($etag) ? '' : 'File uploaded successfully.',
            'etag' => $etag,
            'httpcode' => $info['http_code'],
            'response' => $response,
            'firstByte' => $firstByte,
            'lastByte' => $lastByte,
            'fileSize' => $fileSize
        ];
    }
    
}

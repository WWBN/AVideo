<?php

class SocialUploader
{
    public static function upload($publisher_user_preferences_id, $videoPath, $title, $description, $visibility = 'public', $isShort = false)
    {
        $title = strip_tags($title);
        $description = strip_tags($description);
        $accessToken = SocialMediaPublisher::getRevalidatedToken($publisher_user_preferences_id, true);
        if (empty($accessToken)) {
            return array('error' => true, 'msg' => 'Empty access token', 'line' => __LINE__);
        }
        $pub = new Publisher_user_preferences($publisher_user_preferences_id);
        _error_log("SocialMediaPublisher::upload provider=".$pub->getProviderName());
        try {
            switch ($pub->getProviderName()) {
                case SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE['name']:
                    return SocialUploader::uploadYouTube($accessToken, $videoPath, $title, $description, $visibility, $isShort);
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_FACEBOOK['name']:
                    $json = json_decode($pub->getJson());
                    $pageId = $json->{'restream.ypt.me'}->facebook->broadcaster_id;
                    _error_log("SocialMediaPublisher::upload json=".json_encode($json));
                    return SocialUploader::uploadFacebook($accessToken, $pageId, $videoPath, $title, $description, $visibility, $isShort);
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_INSTAGRAM['name']:
                    //return SocialUploader::uploadYouTube($accessToken, $videoPath, $title, $description, $visibility, $isShort);
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_TWITCH['name']:
                    //return SocialUploader::uploadYouTube($accessToken, $videoPath, $title, $description, $visibility, $isShort);
                    break;
                case SocialMediaPublisher::SOCIAL_TYPE_LINKEDIN['name']:
                    $json = json_decode($pub->getJson());
                    $urn = $json->{'restream.ypt.me'}->linkedin->urn;
                    $id = $json->{'restream.ypt.me'}->linkedin->profile_id;
                    $upload = SocialUploader::uploadLinkedIn($accessToken, $urn, $id, $videoPath, $title, $description, $visibility, $isShort);
                    return $upload;
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

    private static function uploadLinkedIn($accessToken, $authorUrn, $id, $videoPath, $title, $description, $visibility = 'PUBLIC', $isShort = false)
    {
        // Step 1: Upload the video
        $uploadResult = LinkedInUploader::upload($accessToken, $authorUrn, $id, $videoPath, $title, $description);

        // Step 2: Check for errors in the upload result
        if ($uploadResult['error']) {
            $uploadResult['msg'][] = 'Video upload failed';
            return  $uploadResult;
        }
        $videoUrn = $uploadResult['videoURN'];
        // Step 4: Publish the video
        $publishResult = LinkedInUploader::publishVideo($accessToken, $authorUrn, $videoUrn, $title, $description, $visibility, $isShort);
        $uploadResult['publishResult'] = $publishResult;
        // Step 5: Check for errors in the publish result
        if ($publishResult['error']) {
            $uploadResult['error'] = true;
            $uploadResult['msg'][] = 'Video publish failed: ' . $publishResult['message'];
            $uploadResult['msg'][] = json_encode($publishResult['response']);
        } else {
            $uploadResult['msg'][] = 'Video published successfully!';
        }

        return  $uploadResult;
    }
    private static function uploadYouTube($accessToken, $videoPath, $title, $description, $visibility = 'public', $isShort = false)
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
        $video->getStatus()->setPrivacyStatus($visibility);

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

    private static function uploadFacebook($accessToken, $pageId, $videoPath, $title, $description, $visibility = 'public', $isShort = false)
    {
        if ($isShort) {
            _error_log("SocialMediaPublisher::uploadFacebook Short");
            return FacebookUploader::uploadFacebookReels($accessToken, $pageId, $videoPath, $title, $description);
        } else {
            _error_log("SocialMediaPublisher::uploadFacebook Video");
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
        $VideoUploadResponse = FacebookUploader::uploadDirectHostedVideo($pageId, $accessToken, $videoUrl, $title,PHP_EOL.$description);
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

    static function uploadDirectLocalVideo($pageId, $accessToken, $filePath, $description = '')
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

    static function uploadDirectHostedVideo($pageId, $accessToken, $videoUrl, $description = '')
    {
        $url = "https://graph.facebook.com/{$pageId}/videos";
        $postData = [
            'description' => $description,
            'access_token' => $accessToken,
            'file_url' => $videoUrl
        ];

        _error_log("SocialMediaPublisher::uploadDirectHostedVideo $url ".json_encode($postData));

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
    // Version number in the format YYYYMM
    const versionNumber = '202404';

    static function extractRangeFromFile($videoPath, $firstByte, $lastByte)
    {
        // Open the video file
        $fileHandle = fopen($videoPath, 'rb');
        if (!$fileHandle) {
            _error_log("Failed to open video file: $videoPath");
            return false; // Failed to open file
        }

        // Seek to the starting byte
        fseek($fileHandle, $firstByte);

        // Calculate the length of the range
        $rangeLength = $lastByte - $firstByte + 1;

        // Read the specified range from the file
        $rangeContent = fread($fileHandle, $rangeLength);

        // Close the file handle
        fclose($fileHandle);

        // Create a temporary file to store the range content
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'video_range_');
        if (!$tmpFilePath) {
            _error_log("Failed to create temporary file for video range.");
            return false; // Failed to create temporary file
        }

        // Write the range content to the temporary file
        file_put_contents($tmpFilePath, $rangeContent);

        // Return the path to the temporary file
        return $tmpFilePath;
    }

    static function upload($accessToken, $urn, $id, $videoPath, $title, $description)
    {
        $return = [
            'error' => true,
            'msg' => [],
            'initResponse' => null,
            'uploadResponse' => [],
            'finalizeResponse' => null
        ];

        $fileSizeBytes = filesize($videoPath);
        _error_log("LinkedInUploader: File size in bytes: $fileSizeBytes");

        // Initialize upload session
        $initResponse = self::initializeLinkedInUploadSession($accessToken, $urn, $fileSizeBytes);
        $return['initResponse'] = $initResponse;

        // Log the initial response
        _error_log("LinkedInUploader: Initialize Upload Session Response:\n" . print_r($initResponse, true));

        if ($initResponse['error']) {
            $return['msg'][] = "Failed to initialize upload session: " . $initResponse['message'];
            _error_log("LinkedInUploader: Error initializing upload session: " . $initResponse['message']);
            return $return;
        }

        if (empty($initResponse['response']['value']['uploadInstructions'])) {
            $return['msg'][] = "No upload instructions received.";
            _error_log("LinkedInUploader: No upload instructions in initResponse.");
            return $return;
        }

        // Extract uploadToken and videoURN
        $uploadToken = $initResponse['response']['value']['uploadToken'];
        $videoURN = $initResponse['response']['value']['video'];

        // Log the uploadToken and videoURN
        _error_log("LinkedInUploader: Upload Token: $uploadToken");
        _error_log("LinkedInUploader: Video URN: $videoURN");

        $uploadInstructions = $initResponse['response']['value']['uploadInstructions'];

        $uploadedPartIds = [];
        foreach ($uploadInstructions as $instruction) {
            _error_log("LinkedInUploader: Uploading part: " . print_r($instruction, true));
            $tmpFile = self::extractRangeFromFile($videoPath, $instruction['firstByte'], $instruction['lastByte']);

            if (!$tmpFile) {
                $return['msg'][] = "Failed to extract range from file.";
                _error_log("LinkedInUploader: Failed to extract range from file.");
                return $return;
            }

            $uploadResponse = self::uploadVideoToLinkedIn($instruction['uploadUrl'], $tmpFile);
            $return['uploadResponse'][] = $uploadResponse;

            // Log the upload response
            _error_log("LinkedInUploader: Upload Response:\n" . print_r($uploadResponse, true));

            if ($uploadResponse['error']) {
                $return['msg'][] = "Error uploading video part: " . $uploadResponse['msg'];
                _error_log("LinkedInUploader: Error uploading video part: " . $uploadResponse['msg']);
                return $return; // Exit on first upload error
            } else {
                $uploadedPartIds[] = $uploadResponse['etag'];
            }

            // Remove temporary file
            unlink($tmpFile);
        }

        $return['uploadedPartIds'] = $uploadedPartIds;
        $return['videoURN'] = $videoURN;

        // Log the uploadedPartIds
        _error_log("LinkedInUploader: Uploaded Part IDs:\n" . print_r($uploadedPartIds, true));

        // Finalize upload session with uploadToken
        $finalizeResponse = self::finalizeLinkedInUploadSession($accessToken, $videoURN, $uploadToken, $uploadedPartIds);
        $return['finalizeResponse'] = $finalizeResponse;

        // Log the finalize response
        _error_log("LinkedInUploader: Finalize Upload Session Response:\n" . print_r($finalizeResponse, true));

        if ($finalizeResponse['error']) {
            $return['msg'][] = "Failed to finalize upload session: " . $finalizeResponse['message'];
            _error_log("LinkedInUploader: Error finalizing upload session: " . $finalizeResponse['message']);
        } else {
            $return['error'] = false;
        }

        return $return;
    }

    static function finalizeLinkedInUploadSession($accessToken, $videoURN, $uploadToken, $uploadedPartIds)
    {
        $url = "https://api.linkedin.com/rest/videos?action=finalizeUpload";
        $data = json_encode([
            "finalizeUploadRequest" => [
                "video" => $videoURN,
                "uploadToken" => $uploadToken,
                "uploadedPartIds" => $uploadedPartIds
            ]
        ]);

        // Log the request data
        _error_log("Finalize Upload Session Request Data:\n" . $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$accessToken}",
            "X-RestLi-Protocol-Version: 2.0.0",
            "LinkedIn-Version: " . self::versionNumber
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        // Log the raw response
        _error_log("Finalize Upload Session Raw Response:\n" . $response);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        _error_log("Finalize Upload Session HTTP Code: $httpCode");

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            _error_log("cURL error in finalizeLinkedInUploadSession: $error_msg");
            return ['error' => true, 'message' => $error_msg];
        }
        curl_close($ch);

        // Parse the response
        $responseArray = json_decode($response, true);

        // Log the parsed response
        _error_log("Finalize Upload Session Parsed Response:\n" . print_r($responseArray, true));

        // Check if HTTP status code is 200
        if ($httpCode === 200) {
            return [
                'error' => false,
                'httpCode' => $httpCode,
                'response' => $responseArray
            ];
        } else {
            _error_log("Finalize upload session failed with HTTP code: $httpCode");
            return [
                'error' => true,
                'httpCode' => $httpCode,
                'message' => 'HTTP error code: ' . $httpCode,
                'response' => $responseArray
            ];
        }
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

        // Log the request data
        _error_log("Initialize Upload Session Request Data:\n" . $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$accessToken}",
            "X-RestLi-Protocol-Version: 2.0.0",
            "LinkedIn-Version: " . self::versionNumber
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        // Log the raw response
        _error_log("Initialize Upload Session Raw Response:\n" . $response);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        _error_log("Initialize Upload Session HTTP Code: $httpCode");

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            _error_log("cURL error in initializeLinkedInUploadSession: $error_msg");
            return ['error' => true, 'message' => $error_msg];
        }
        curl_close($ch);

        // Parse the response
        $responseArray = json_decode($response, true);

        // Log the parsed response
        _error_log("Initialize Upload Session Parsed Response:\n" . print_r($responseArray, true));

        // Check if HTTP status code is 200
        if ($httpCode === 200) {
            return ['error' => false, 'response' => $responseArray];
        } else {
            _error_log("Initialize upload session failed with HTTP code: $httpCode");
            return [
                'error' => true,
                'message' => 'HTTP error code: ' . $httpCode,
                'response' => $responseArray
            ];
        }
    }

    static function uploadVideoToLinkedIn($uploadUrl, $filePath)
    {
        $shellCmd = 'curl -v -H "Content-Type:application/octet-stream" --upload-file "' .
            $filePath . '" "' .
            $uploadUrl . '" 2>&1';

        // Log the shell command
        _error_log("Upload Video Shell Command:\n" . $shellCmd);

        exec($shellCmd, $o);

        // Log the output
        _error_log("Upload Video Command Output:\n" . implode("\n", $o));

        $matches = [];
        preg_match('/(etag:)(\s?)(.*)(\n)/i', implode("\n", $o), $matches);
        $etag = isset($matches[3]) ? trim($matches[3]) : null;

        // Log the extracted ETag
        _error_log("Extracted ETag: $etag");

        return [
            'uploadUrl' => $uploadUrl,
            'error' => empty($etag),
            'msg' => empty($etag) ? 'Failed to upload part.' : 'File uploaded successfully.',
            'etag' => $etag,
            'header' => implode(PHP_EOL, $o)
        ];
    }

    /*
    Visibility restrictions on content. Type of MemberNetworkVisibility which has the values of:
    CONNECTIONS - Represents 1st degree network of owner.
    PUBLIC - Anyone can view this.
    LOGGED_IN - Viewable by logged in members only.
    CONTAINER - Visibility is delegated to the owner of the container entity. For example, posts within a group are delegated to the groups authorization API for visibility authorization.
    */
    static function publishVideo($accessToken, $authorUrn, $videoUrn, $title, $description, $visibility = 'PUBLIC', $isShort = false)
    {
        $url = "https://api.linkedin.com/rest/posts";

        $data = [
            "author" => $authorUrn,
            "commentary" => $description,
            "visibility" => "PUBLIC",
            "distribution" => [
                "feedDistribution" => "MAIN_FEED",
                "targetEntities" => [],
                "thirdPartyDistributionChannels" => []
            ],
            "content" => [
                "media" => [
                    "title" => $title,
                    "id" => $videoUrn
                ]
            ],
            "lifecycleState" => "PUBLISHED",
            "isReshareDisabledByAuthor" => false
        ];

        $dataString = json_encode($data);

        // Log the request data
        _error_log("Publish Video Request Data:\n" . $dataString);

        $ch = curl_init($url);

        // Collect headers and response
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json",
            "LinkedIn-Version: " . self::versionNumber,
            "X-RestLi-Protocol-Version: 2.0.0"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // Get headers and response body

        $response = curl_exec($ch);

        // Separate headers and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size); // Extract headers
        $body = substr($response, $header_size); // Extract response body

        // Log headers and body separately
        _error_log("Publish Video Headers:\n" . $headers);
        _error_log("Publish Video Body:\n" . $body);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
        _error_log("Publish Video HTTP Code: $httpCode");

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            _error_log("cURL error in publishVideo: $error_msg");
            return ['error' => true, 'message' => $error_msg];
        }

        curl_close($ch);

        // Parse the headers to find x-restli-id
        $xRestLiId = null;
        if (preg_match('/x-restli-id: (.*)\r/', $headers, $matches)) {
            $xRestLiId = trim($matches[1]); // Get x-restli-id from headers
        }

        // Log the x-restli-id
        _error_log("x-restli-id: " . $xRestLiId);

        // Parse the response body
        $responseArray = json_decode($body, true);

        // Log the parsed response
        _error_log("Publish Video Parsed Response:\n" . print_r($responseArray, true));

        // Check if HTTP status code is 201 (Created)
        if ($httpCode === 201) {
            return [
                'error' => false,
                'httpCode' => $httpCode,
                'xRestLiId' => $xRestLiId,
                'response' => $responseArray
            ];
        } else {
            _error_log("Publish video failed with HTTP code: $httpCode");
            return [
                'error' => true,
                'message' => 'HTTP error code: ' . $httpCode,
                'httpCode' => $httpCode,
                'xRestLiId' => $xRestLiId,
                'response' => $responseArray
            ];
        }
    }
}

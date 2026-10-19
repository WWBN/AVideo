<?php

/**
 * Publishes AVideo videos to the user's YouTube channel (YouTube Data API v3, videos.insert).
 *
 * - OAuth: the Google client ID/secret live on the restream.ypt.me broker, never on this site. The
 *   connection row (publisher_user_preferences.json) keeps the broker's token; it is renewed through
 *   SocialMediaPublisher::revalidateTokenAndSave(). Only the access token is given to Google_Client:
 *   with a refresh_token it would try to refresh by itself with a client secret this site does not have.
 * - The upload runs in a background CLI worker (youtubeUploadWorker.php) so it is not bound to the
 *   browser request. Progress and the result are kept in publisher_video_publisher_logs.details as a
 *   "job" object; status is STATUS_PROCESSING while running, STATUS_VERIFIED after the upload, and
 *   STATUS_INACTIVE when it failed or was canceled.
 * - Error messages are stored as codes and translated when shown, so each user sees their language.
 */
class YouTubePublisher
{
    const STATE_QUEUED = 'queued';
    const STATE_PREPARING = 'preparing';
    const STATE_UPLOADING = 'uploading';
    const STATE_PROCESSING = 'processing'; // uploaded, YouTube is still processing it
    const STATE_PUBLISHED = 'published';   // YouTube finished processing it
    const STATE_FAILED = 'failed';
    const STATE_INTERRUPTED = 'interrupted';
    const STATE_CANCELED = 'canceled';

    const CHUNK_SIZE = 8388608; // 8 MB, resumable chunks must be multiples of 256 KB
    const TITLE_MAX_CHARS = 100;
    const DESCRIPTION_MAX_BYTES = 5000;
    const TAGS_MAX_CHARS = 500;
    const TOKEN_REFRESH_MARGIN = 300;
    const QUEUED_START_TIMEOUT = 120;
    const MAX_TRANSIENT_RETRIES = 6;
    const PROCESSING_CHECK_INTERVAL = 30;
    const PROCESSING_CHECK_MAX_AGE = 86400;
    const MAX_AUTO_RESUMES = 3;
    const HEARTBEAT_TIMEOUT = 600;

    const PRIVACY_OPTIONS = array('private', 'unlisted', 'public');

    // codes that "Try again" can fix without editing anything
    const RETRYABLE_CODES = array('reconnect', 'authServiceUnavailable', 'quota', 'channelLimit', 'rateLimit', 'network', 'interrupted', 'didNotStart', 'unknown', 'notConverted');
    const RECONNECT_CODES = array('reconnect', 'permission', 'connectionMissing');

    public static function isYouTubeProvider($providerName)
    {
        return $providerName === SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE['name'];
    }

    /* ------------------------------------------------------------------
     * Metadata (pure functions)
     * ---------------------------------------------------------------- */

    public static function htmlToText($text)
    {
        $text = (string) $text;
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text);
        $text = preg_replace('/<\/\s*(p|div|li|h[1-6])\s*>/i', "\n", $text);
        $text = preg_replace('/<!--.*?-->/s', '', $text);
        // only real tags: strip_tags() would also eat "<3" and everything after it
        $text = preg_replace('/<\/?[a-z][^<>]*>/i', '', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // YouTube rejects titles and descriptions containing < or >
        return str_replace(array('<', '>'), '', $text);
    }

    private static function cleanTitle($title)
    {
        $title = trim(preg_replace('/\s+/u', ' ', self::htmlToText($title)));
        return mb_substr($title, 0, self::TITLE_MAX_CHARS, 'UTF-8');
    }

    public static function sanitizeTitle($title, $fallback = '')
    {
        $title = self::cleanTitle($title);
        if ($title === '') {
            $title = self::cleanTitle($fallback);
        }
        // YouTube rejects an empty title
        return $title === '' ? 'Untitled' : $title;
    }

    /**
     * Only real videos have a file YouTube can take. Very old rows can have an empty type.
     * @return string empty when supported, otherwise the message code
     */
    public static function getUnsupportedTypeCode($type)
    {
        if (in_array($type, array('embed', 'linkVideo'), true)) {
            return 'embedded';
        }
        if (!empty($type) && !in_array($type, array('video', 'short'), true)) {
            return 'unsupportedType';
        }
        return '';
    }

    public static function sanitizeDescription($description)
    {
        $description = self::htmlToText($description);
        $description = str_replace(array("\r\n", "\r"), "\n", $description);
        $description = preg_replace("/[ \t]+\n/", "\n", $description);
        $description = trim(preg_replace("/\n{3,}/", "\n\n", $description));
        if (strlen($description) > self::DESCRIPTION_MAX_BYTES) {
            // the limit is in bytes; mb_strcut never splits a multibyte character
            $description = mb_strcut($description, 0, self::DESCRIPTION_MAX_BYTES, 'UTF-8');
        }
        return $description;
    }

    /**
     * @param string|array $tags comma separated string or list
     * @return array tags whose total length fits YouTube's 500 character limit
     */
    public static function sanitizeTags($tags)
    {
        if (!is_array($tags)) {
            $tags = explode(',', (string) $tags);
        }
        $result = array();
        $seen = array();
        $total = 0;
        foreach ($tags as $tag) {
            if (!is_scalar($tag)) {
                continue;
            }
            $tag = self::htmlToText($tag);
            $tag = str_replace(array(',', '"'), ' ', $tag);
            $tag = trim(preg_replace('/\s+/u', ' ', $tag));
            if ($tag === '') {
                continue;
            }
            $key = mb_strtolower($tag, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }
            // YouTube counts the comma between tags and the quotes around a tag with spaces
            $length = mb_strlen($tag, 'UTF-8') + (strpos($tag, ' ') !== false ? 2 : 0) + (empty($result) ? 0 : 1);
            if ($total + $length > self::TAGS_MAX_CHARS) {
                continue;
            }
            $total += $length;
            $seen[$key] = true;
            $result[] = $tag;
        }
        return $result;
    }

    public static function sanitizePrivacy($privacy)
    {
        $privacy = strtolower(trim((string) $privacy));
        return in_array($privacy, self::PRIVACY_OPTIONS, true) ? $privacy : 'private';
    }

    public static function sanitizeCategoryId($categoryId)
    {
        $categoryId = trim((string) $categoryId);
        return preg_match('/^[0-9]{1,5}$/', $categoryId) ? $categoryId : '';
    }

    public static function buildMetadata(array $input, $fallbackTitle = '')
    {
        return array(
            'title' => self::sanitizeTitle(isset($input['title']) ? $input['title'] : '', $fallbackTitle),
            'description' => self::sanitizeDescription(isset($input['description']) ? $input['description'] : ''),
            'tags' => self::sanitizeTags(isset($input['tags']) ? $input['tags'] : array()),
            'categoryId' => self::sanitizeCategoryId(isset($input['categoryId']) ? $input['categoryId'] : ''),
            'privacy' => self::sanitizePrivacy(isset($input['privacy']) ? $input['privacy'] : ''),
        );
    }

    /* ------------------------------------------------------------------
     * Errors (pure functions)
     * ---------------------------------------------------------------- */

    /**
     * Reads the HTTP code and the first reason from a Google API error.
     * @param \Throwable|string $error exception or raw JSON body
     */
    public static function parseGoogleError($error, $httpCode = 0)
    {
        $reasons = array();
        $body = '';
        if ($error instanceof \Throwable) {
            if ($error instanceof \Google\Service\Exception) {
                $httpCode = $error->getCode();
                foreach ((array) $error->getErrors() as $item) {
                    if (!empty($item['reason'])) {
                        $reasons[] = $item['reason'];
                    }
                }
            }
            $body = $error->getMessage();
        } else {
            $body = (string) $error;
        }
        $json = json_decode($body, true);
        if (is_array($json) && !empty($json['error'])) {
            if (!empty($json['error']['errors']) && is_array($json['error']['errors'])) {
                foreach ($json['error']['errors'] as $item) {
                    if (!empty($item['reason'])) {
                        $reasons[] = $item['reason'];
                    }
                }
            }
            if (!empty($json['error']['status']) && is_string($json['error']['status'])) {
                $reasons[] = $json['error']['status'];
            }
            if (is_string($json['error'])) {
                // OAuth style: {"error":"invalid_grant"}
                $reasons[] = $json['error'];
            }
            if (empty($httpCode) && !empty($json['error']['code'])) {
                $httpCode = intval($json['error']['code']);
            }
        }
        if (empty($httpCode) && preg_match('/HTTP ([0-9]{3})/', $body, $matches)) {
            $httpCode = intval($matches[1]);
        }
        return array('httpCode' => intval($httpCode), 'reason' => empty($reasons) ? '' : $reasons[0]);
    }

    /**
     * Turns an HTTP code + YouTube reason into one of the message codes of getMessage().
     */
    public static function classifyGoogleError($httpCode, $reason)
    {
        $httpCode = intval($httpCode);
        switch ($reason) {
            case 'quotaExceeded':
            case 'dailyLimitExceeded':
                return 'quota';
            case 'uploadLimitExceeded':
                return 'channelLimit';
            case 'rateLimitExceeded':
            case 'userRateLimitExceeded':
            case 'RESOURCE_EXHAUSTED':
                return 'rateLimit';
            case 'youtubeSignupRequired':
                return 'noChannel';
            case 'insufficientPermissions':
            case 'ACCESS_TOKEN_SCOPE_INSUFFICIENT':
            case 'forbidden':
                return 'permission';
            case 'invalidTitle':
                return 'invalidTitle';
            case 'invalidDescription':
                return 'invalidDescription';
            case 'invalidTags':
                return 'invalidTags';
            case 'invalidCategoryId':
                return 'invalidCategory';
            case 'forbiddenPrivacySetting':
                return 'invalidPrivacy';
            case 'invalidVideoMetadata':
            case 'defaultLanguageNotSet':
            case 'invalidFilename':
                return 'invalidMetadata';
            case 'accessNotConfigured':
            case 'keyInvalid':
            case 'SERVICE_DISABLED':
            case 'invalid_client':
                return 'misconfigured';
            case 'authError':
            case 'invalid_grant':
            case 'UNAUTHENTICATED':
                return 'reconnect';
        }
        if ($httpCode === 401) {
            return 'reconnect';
        }
        if ($httpCode === 429) {
            return 'rateLimit';
        }
        if ($httpCode >= 500) {
            return 'network';
        }
        if ($httpCode === 403) {
            return 'permission';
        }
        return 'unknown';
    }

    public static function isTransientHttpCode($httpCode)
    {
        return in_array(intval($httpCode), array(408, 429, 500, 502, 503, 504), true);
    }

    /**
     * Maps a processed videos.list item (array) to a job state and message code.
     */
    public static function processingResult(array $item)
    {
        $status = isset($item['status']) ? $item['status'] : array();
        $uploadStatus = isset($status['uploadStatus']) ? $status['uploadStatus'] : '';
        switch ($uploadStatus) {
            case 'processed':
                return array('state' => self::STATE_PUBLISHED, 'code' => '', 'detail' => '');
            case 'failed':
                $reason = isset($status['failureReason']) ? $status['failureReason'] : '';
                return array('state' => self::STATE_FAILED, 'code' => 'processingFailed', 'detail' => $reason);
            case 'rejected':
                $reason = isset($status['rejectionReason']) ? $status['rejectionReason'] : '';
                $code = 'rejectedPolicy';
                if ($reason === 'duplicate') {
                    $code = 'rejectedDuplicate';
                } elseif ($reason === 'copyright' || $reason === 'claim') {
                    $code = 'rejectedCopyright';
                } elseif ($reason === 'length') {
                    $code = 'rejectedLength';
                }
                return array('state' => self::STATE_FAILED, 'code' => $code, 'detail' => $reason);
            case 'deleted':
                return array('state' => self::STATE_FAILED, 'code' => 'deletedOnYouTube', 'detail' => 'deleted');
        }
        return array('state' => self::STATE_PROCESSING, 'code' => '', 'detail' => $uploadStatus);
    }

    /**
     * Translated, HTML-safe text for a state or message code.
     */
    public static function getMessage($code)
    {
        switch ($code) {
            case self::STATE_QUEUED:
                return __('Waiting to start', true);
            case self::STATE_PREPARING:
                return __('Preparing the video file', true);
            case self::STATE_UPLOADING:
                return __('Uploading to YouTube', true);
            case self::STATE_PROCESSING:
                return __('Uploaded. YouTube is processing the video, this can take a few minutes.', true);
            case self::STATE_PUBLISHED:
                return __('Published on YouTube', true);
            case self::STATE_CANCELED:
                return __('The upload was canceled.', true);
            case 'interrupted':
                return __('The upload was interrupted. Click Resume to continue from where it stopped.', true);
            case 'didNotStart':
                return __('The upload did not start. Click Resume to try again.', true);
            case 'reconnect':
                return __('Your YouTube authorization expired or was revoked. Reconnect your YouTube account and try again.', true);
            case 'authServiceUnavailable':
                return __('The YouTube authorization could not be renewed because the authorization service did not answer. Please try again in a few minutes.', true);
            case 'connectionMissing':
                return __('This YouTube connection no longer exists. Connect your YouTube account again.', true);
            case 'noChannel':
                return __('This Google account does not have a YouTube channel yet. Create a channel on YouTube, then try again.', true);
            case 'quota':
                return __('The daily YouTube upload quota of this website was reached. Uploads become available again after midnight Pacific Time. Please try again later.', true);
            case 'channelLimit':
                return __('Your YouTube channel reached its daily upload limit. Please try again in 24 hours.', true);
            case 'rateLimit':
                return __('YouTube is receiving too many requests right now. Please wait a few minutes and try again.', true);
            case 'permission':
                return __('YouTube did not allow this upload. Reconnect your YouTube account and allow this website to manage your YouTube videos.', true);
            case 'invalidTitle':
                return __('YouTube did not accept the title. Edit the title and publish again.', true);
            case 'invalidDescription':
                return __('YouTube did not accept the description. Edit the description and publish again.', true);
            case 'invalidTags':
                return __('YouTube did not accept the tags. Edit the tags and publish again.', true);
            case 'invalidCategory':
                return __('YouTube did not accept the selected category. Choose another category and publish again.', true);
            case 'invalidPrivacy':
                return __('YouTube did not accept the selected privacy setting. Choose another one and publish again.', true);
            case 'invalidMetadata':
                return __('YouTube did not accept the video details. Check the title, description and tags, then publish again.', true);
            case 'misconfigured':
                return __('The YouTube integration is not configured correctly. Please contact the site administrator.', true);
            case 'network':
                return __('The connection to YouTube was interrupted. The upload was paused and can be resumed.', true);
            case 'sourceMissing':
                return __('The video file could not be found on this server, so it cannot be sent to YouTube.', true);
            case 'unsupportedType':
                return __('This type of media cannot be published to YouTube. Only videos with a video file can be published.', true);
            case 'embedded':
                return __('This video is embedded from another website, so there is no video file to send to YouTube.', true);
            case 'notConverted':
                return __('This video is only stored in streaming (HLS) format and could not be converted to MP4 for YouTube. Please try again later or contact the site administrator.', true);
            case 'backgroundDisabled':
                return __('This server cannot run background tasks, so the upload could not start. Please contact the site administrator.', true);
            case 'processingFailed':
                return __('YouTube could not process the uploaded file. Check that the video plays correctly on this site and publish it again.', true);
            case 'rejectedDuplicate':
                return __('YouTube rejected the video because it is a duplicate of a video already on your channel.', true);
            case 'rejectedCopyright':
                return __('YouTube rejected the video because of a copyright claim.', true);
            case 'rejectedLength':
                return __('YouTube rejected the video because it is longer than your channel allows. Verify your YouTube account to upload longer videos.', true);
            case 'rejectedPolicy':
                return __('YouTube rejected the video because it does not follow the YouTube policies.', true);
            case 'deletedOnYouTube':
                return __('This video is no longer available on YouTube. It may have been deleted.', true);
        }
        return __('YouTube returned an unexpected error. Please try again later. If the problem continues, contact the site administrator.', true);
    }

    /* ------------------------------------------------------------------
     * Job presentation (pure functions)
     * ---------------------------------------------------------------- */

    public static function isActiveState($state)
    {
        return in_array($state, array(self::STATE_QUEUED, self::STATE_PREPARING, self::STATE_UPLOADING), true);
    }

    /**
     * State the user should see. A job marked as running whose worker is gone was interrupted.
     * @param bool|null $workerRunning null when it cannot be told (the heartbeat decides)
     */
    public static function getEffectiveState(array $job, $workerRunning, $now)
    {
        $state = isset($job['state']) ? $job['state'] : self::STATE_FAILED;
        if (!self::isActiveState($state)) {
            return $state;
        }
        if ($workerRunning === null) {
            $heartbeat = !empty($job['updatedAt']) ? intval($job['updatedAt']) : 0;
            $workerRunning = ($now - $heartbeat) < self::HEARTBEAT_TIMEOUT;
        }
        if ($workerRunning) {
            return $state;
        }
        if ($state === self::STATE_QUEUED) {
            $queuedAt = !empty($job['queuedAt']) ? intval($job['queuedAt']) : 0;
            if (($now - $queuedAt) < self::QUEUED_START_TIMEOUT) {
                return $state;
            }
        }
        return self::STATE_INTERRUPTED;
    }

    /**
     * What the browser receives: no resume URI, no technical detail, no token.
     */
    public static function getPublicJob(array $job, $workerRunning, $now = 0)
    {
        if (empty($now)) {
            $now = time();
        }
        $state = self::getEffectiveState($job, $workerRunning, $now);
        $code = isset($job['code']) ? $job['code'] : '';
        if ($state === self::STATE_INTERRUPTED) {
            if (empty($code) || !in_array($code, self::RETRYABLE_CODES, true)) {
                $code = (isset($job['state']) && $job['state'] === self::STATE_QUEUED) ? 'didNotStart' : 'interrupted';
            }
        }
        $isError = in_array($state, array(self::STATE_FAILED, self::STATE_INTERRUPTED), true);
        $message = $isError ? self::getMessage($code) : self::getMessage($state);
        $total = !empty($job['totalBytes']) ? intval($job['totalBytes']) : 0;
        $uploaded = !empty($job['uploadedBytes']) ? intval($job['uploadedBytes']) : 0;
        $percent = 0;
        if (in_array($state, array(self::STATE_PROCESSING, self::STATE_PUBLISHED), true)) {
            $percent = 100;
        } elseif ($total > 0) {
            $percent = min(99, floor(($uploaded / $total) * 100));
        }
        $retryable = $isError && in_array($code, self::RETRYABLE_CODES, true);
        return array(
            'state' => $state,
            'code' => $isError ? $code : '',
            'message' => $message,
            'isError' => $isError,
            'isActive' => self::isActiveState($state),
            'percent' => $percent,
            'uploadedBytes' => $uploaded,
            'totalBytes' => $total,
            'url' => !empty($job['url']) ? $job['url'] : '',
            'title' => isset($job['metadata']['title']) ? $job['metadata']['title'] : '',
            'privacy' => isset($job['metadata']['privacy']) ? $job['metadata']['privacy'] : '',
            'accountName' => isset($job['accountName']) ? $job['accountName'] : '',
            'canResume' => $retryable && !in_array($code, array('connectionMissing'), true),
            'canCancel' => self::isActiveState($state) || $state === self::STATE_INTERRUPTED,
            'needsReconnect' => $isError && in_array($code, self::RECONNECT_CODES, true),
            'updatedAt' => !empty($job['updatedAt']) ? intval($job['updatedAt']) : 0,
        );
    }

    /**
     * Older uploads (before the background worker) only stored the API response.
     */
    public static function getLegacyJob(array $details)
    {
        $response = isset($details['response']) ? $details['response'] : null;
        if (is_array($response) && !empty($response['id']) && is_string($response['id'])) {
            return array(
                'state' => self::STATE_PUBLISHED,
                'youtubeId' => $response['id'],
                'url' => 'https://youtu.be/' . rawurlencode($response['id']),
                'legacy' => true,
            );
        }
        return array('state' => self::STATE_FAILED, 'code' => 'unknown', 'legacy' => true);
    }

    public static function getJobFromDetails($details)
    {
        if (is_string($details)) {
            $details = json_decode($details, true);
        }
        if (!is_array($details)) {
            return array();
        }
        if (!empty($details['job']) && is_array($details['job'])) {
            return $details['job'];
        }
        return self::getLegacyJob($details);
    }

    /**
     * Removes values that must never reach the browser (admin log view included).
     */
    public static function redactDetails($details)
    {
        $isString = is_string($details);
        $array = $isString ? json_decode($details, true) : $details;
        if (!is_array($array)) {
            return $details;
        }
        if (isset($array['job']) && is_array($array['job'])) {
            unset($array['job']['resumeUri']);
        }
        return $isString ? json_encode($array) : $array;
    }

    /* ------------------------------------------------------------------
     * Token handling
     * ---------------------------------------------------------------- */

    public static function getTokenExpiresAt($json)
    {
        if (is_string($json)) {
            $json = json_decode($json);
        }
        $broker = (is_object($json) && !empty($json->{'restream.ypt.me'})) ? $json->{'restream.ypt.me'} : null;
        if (empty($broker)) {
            return 0;
        }
        if (!empty($broker->accessToken->expires_at)) {
            return intval($broker->accessToken->expires_at);
        }
        if (!empty($broker->expires->expires_at)) {
            return intval($broker->expires->expires_at);
        }
        return 0;
    }

    public static function tokenNeedsRefresh($expiresAt, $now, $margin = self::TOKEN_REFRESH_MARGIN)
    {
        return !empty($expiresAt) && ($expiresAt - $now) < $margin;
    }

    /**
     * Returns a valid access token, renewing it through the broker when it is about to expire.
     * @throws YouTubePublisherException
     */
    public static function getAccessToken($publisher_user_preferences_id, $forceRefresh = false)
    {
        $row = Publisher_user_preferences::getFromDb($publisher_user_preferences_id, true);
        if (empty($row) || empty($row['id'])) {
            throw new YouTubePublisherException('connectionMissing', 'connection row not found');
        }
        $json = json_decode($row['json']);
        $broker = (is_object($json) && !empty($json->{'restream.ypt.me'})) ? $json->{'restream.ypt.me'} : null;
        $token = !empty($broker->accessToken->access_token) ? $broker->accessToken->access_token : '';
        $expiresAt = self::getTokenExpiresAt($json);

        if ($forceRefresh || empty($token) || self::tokenNeedsRefresh($expiresAt, time())) {
            if (empty($broker->accessToken->refresh_token)) {
                throw new YouTubePublisherException('reconnect', 'no refresh token, expires_at=' . $expiresAt);
            }
            $resp = SocialMediaPublisher::revalidateTokenAndSave($publisher_user_preferences_id, false);
            if (!empty($resp->error)) {
                $brokerAnswered = !empty($resp->revalidateToken->respJson);
                $msg = isset($resp->msg) && is_string($resp->msg) ? $resp->msg : '';
                throw new YouTubePublisherException($brokerAnswered ? 'reconnect' : 'authServiceUnavailable', 'token renewal failed: ' . $msg);
            }
            $row = Publisher_user_preferences::getFromDb($publisher_user_preferences_id, true);
            $json = json_decode($row['json']);
            $token = !empty($json->{'restream.ypt.me'}->accessToken->access_token) ? $json->{'restream.ypt.me'}->accessToken->access_token : '';
        }
        if (empty($token)) {
            throw new YouTubePublisherException('reconnect', 'empty access token');
        }
        return $token;
    }

    public static function createClient($accessToken)
    {
        $client = new Google_Client();
        $client->setApplicationName('AVideo');
        // only the access token: see the class comment
        $client->setAccessToken(array('access_token' => $accessToken));
        return $client;
    }

    /* ------------------------------------------------------------------
     * Jobs (database)
     * ---------------------------------------------------------------- */

    public static function getLockDir()
    {
        $dir = getVideosDir() . 'cache' . DIRECTORY_SEPARATOR . 'SocialMediaPublisher' . DIRECTORY_SEPARATOR;
        make_path($dir);
        return $dir;
    }

    private static function openLock($name)
    {
        $file = self::getLockDir() . $name . '.lock';
        $isNew = !file_exists($file);
        $fp = @fopen($file, 'c');
        if ($fp && $isNew) {
            // the web server and the cron user must both be able to open it
            @chmod($file, 0666);
        }
        return $fp;
    }

    /**
     * @return bool|null true running, false not running, null cannot tell
     */
    public static function isWorkerRunning($logs_id)
    {
        $file = self::getLockDir() . 'worker_' . intval($logs_id) . '.lock';
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'c');
        if (!$fp) {
            return null;
        }
        $free = flock($fp, LOCK_EX | LOCK_NB);
        if ($free) {
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        return !$free;
    }

    private static function getCancelFile($logs_id)
    {
        return self::getLockDir() . 'cancel_' . intval($logs_id);
    }

    private static function loadDetails(Publisher_video_publisher_logs $log)
    {
        $details = json_decode($log->getDetails(), true);
        return is_array($details) ? $details : array();
    }

    private static function saveJob(Publisher_video_publisher_logs $log, array $details, array $changes, $status = '')
    {
        $job = isset($details['job']) && is_array($details['job']) ? $details['job'] : array();
        foreach ($changes as $key => $value) {
            if ($value === null) {
                unset($job[$key]);
            } else {
                $job[$key] = $value;
            }
        }
        $job['updatedAt'] = time();
        $details['job'] = $job;
        $log->setDetails($details);
        if (!empty($status)) {
            $log->setStatus($status);
        }
        if (!$log->save()) {
            _error_log('YouTubePublisher::saveJob could not save log ' . $log->getId(), AVideoLog::$ERROR);
        }
        return $details;
    }

    /**
     * Rows of this video that belong to this connection, newest first.
     */
    public static function getJobsForVideoAndConnection($videos_id, $publisher_user_preferences_id, $publisher_social_medias_id)
    {
        $sql = "SELECT id, status, details, publish_datetimestamp FROM publisher_video_publisher_logs WHERE videos_id = ? AND publisher_social_medias_id = ? ORDER BY id DESC LIMIT 100";
        $res = sqlDAL::readSql($sql, 'ii', array(intval($videos_id), intval($publisher_social_medias_id)), true);
        $rows = $res ? sqlDAL::fetchAllAssoc($res) : array();
        sqlDAL::close($res);
        $jobs = array();
        foreach ($rows as $row) {
            $details = json_decode($row['details'], true);
            if (!is_array($details) || empty($details['publisher_user_preferences_id']) || intval($details['publisher_user_preferences_id']) !== intval($publisher_user_preferences_id)) {
                continue;
            }
            $row['job'] = self::getJobFromDetails($details);
            $jobs[] = $row;
        }
        return $jobs;
    }

    /**
     * Validates and queues an upload, then starts the background worker.
     * @return array response for uploadVideo.json.php
     */
    public static function queue($users_id, $publisher_user_preferences_id, $videos_id, array $input, $confirmDuplicate = false)
    {
        $response = array('error' => true, 'msg' => '', 'logs_id' => 0);
        $pref = new Publisher_user_preferences($publisher_user_preferences_id);
        $publisher_social_medias_id = $pref->getPublisher_social_medias_id();

        $video = new Video('', '', $videos_id);
        $metadata = self::buildMetadata($input, $video->getTitle());

        $unsupported = self::getUnsupportedTypeCode($video->getType());
        if (!empty($unsupported)) {
            $response['msg'] = self::getMessage($unsupported);
            return $response;
        }
        try {
            // fail now with "reconnect" instead of queuing a job that fails a few seconds later
            self::getAccessToken($publisher_user_preferences_id);
        } catch (YouTubePublisherException $e) {
            _error_log("YouTubePublisher::queue token check {$e->getErrorCode()} {$e->getMessage()}", AVideoLog::$WARNING);
            $response['msg'] = self::getMessage($e->getErrorCode());
            $response['needsReconnect'] = in_array($e->getErrorCode(), self::RECONNECT_CODES, true);
            return $response;
        }

        $lock = self::openLock('queue_' . intval($publisher_user_preferences_id) . '_' . intval($videos_id));
        if ($lock) {
            flock($lock, LOCK_EX);
        }
        try {
            foreach (self::getJobsForVideoAndConnection($videos_id, $publisher_user_preferences_id, $publisher_social_medias_id) as $row) {
                $public = self::getPublicJob($row['job'], self::isWorkerRunning($row['id']));
                if ($public['isActive']) {
                    $response['msg'] = __('This video is already being published to this YouTube account. Wait for it to finish.', true);
                    $response['alreadyRunning'] = true;
                    $response['logs_id'] = intval($row['id']);
                    return $response;
                }
                if (!$confirmDuplicate && in_array($public['state'], array(self::STATE_PROCESSING, self::STATE_PUBLISHED), true)) {
                    $response['msg'] = __('This video was already published to this YouTube account. Do you want to publish another copy?', true);
                    $response['duplicate'] = true;
                    $response['url'] = $public['url'];
                    $response['publishedAt'] = date('Y-m-d H:i', intval($row['publish_datetimestamp']));
                    return $response;
                }
            }

            $now = time();
            $details = array(
                'publisher_user_preferences_id' => intval($publisher_user_preferences_id),
                'provider' => SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE['name'],
                'job' => array(
                    'state' => self::STATE_QUEUED,
                    'code' => '',
                    'metadata' => $metadata,
                    'accountName' => (string) $pref->getPreferred_profile(),
                    'queuedAt' => $now,
                    'updatedAt' => $now,
                    'uploadedBytes' => 0,
                    'totalBytes' => 0,
                    'attempts' => 0,
                    'autoResumes' => 0,
                ),
            );
            $log = new Publisher_video_publisher_logs(0);
            $log->setPublish_datetimestamp($now);
            $log->setStatus(Publisher_video_publisher_logs::STATUS_PROCESSING);
            $log->setDetails($details);
            $log->setVideos_id($videos_id);
            $log->setUsers_id($users_id);
            $log->setPublisher_social_medias_id($publisher_social_medias_id);
            $logs_id = $log->save();
        } finally {
            if ($lock) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }

        if (empty($logs_id)) {
            _error_log("YouTubePublisher::queue could not save the job videos_id={$videos_id}", AVideoLog::$ERROR);
            $response['msg'] = __('The upload could not be started. Please try again.', true);
            return $response;
        }
        $response['logs_id'] = intval($logs_id);
        if (!self::startWorker($logs_id)) {
            $response['msg'] = self::getMessage('backgroundDisabled');
            return $response;
        }
        $response['error'] = false;
        $response['msg'] = __('The upload to YouTube started. You can follow its progress below, it continues even if you close this window.', true);
        return $response;
    }

    public static function canRunBackgroundTasks()
    {
        if (!function_exists('exec')) {
            return false;
        }
        $disabled = array_map('trim', explode(',', strtolower((string) ini_get('disable_functions'))));
        return !in_array('exec', $disabled, true);
    }

    public static function startWorker($logs_id)
    {
        global $global;
        $logs_id = intval($logs_id);
        if (!self::canRunBackgroundTasks()) {
            $log = new Publisher_video_publisher_logs($logs_id, true);
            self::saveJob($log, self::loadDetails($log), array('state' => self::STATE_FAILED, 'code' => 'backgroundDisabled'), Publisher_video_publisher_logs::STATUS_INACTIVE);
            _error_log('YouTubePublisher::startWorker PHP exec() is disabled, cannot start the upload worker', AVideoLog::$ERROR);
            return false;
        }
        $script = $global['systemRootPath'] . 'plugin/SocialMediaPublisher/youtubeUploadWorker.php';
        execAsync(get_php() . ' ' . escapeshellarg($script) . ' ' . $logs_id);
        return true;
    }

    /**
     * Resume an interrupted job, or try again a failed one that a retry can fix.
     */
    public static function resume($logs_id, $users_id, $isAutomatic = false)
    {
        $response = array('error' => true, 'msg' => '');
        $log = new Publisher_video_publisher_logs($logs_id, true);
        if (empty($log->getId()) || ($log->getUsers_id() !== intval($users_id) && !$isAutomatic)) {
            $response['msg'] = __('Upload not found', true);
            return $response;
        }
        $details = self::loadDetails($log);
        if (empty($details['job'])) {
            $response['msg'] = __('This upload cannot be resumed. Publish the video again.', true);
            return $response;
        }
        $lock = self::openLock('queue_' . intval($details['publisher_user_preferences_id']) . '_' . $log->getVideos_id());
        if ($lock) {
            flock($lock, LOCK_EX);
        }
        try {
            $public = self::getPublicJob($details['job'], self::isWorkerRunning($logs_id));
            if ($public['isActive']) {
                $response['msg'] = __('This upload is still running.', true);
                return $response;
            }
            if (!$public['canResume']) {
                $response['msg'] = __('This upload cannot be resumed. Publish the video again.', true);
                return $response;
            }
            @unlink(self::getCancelFile($logs_id));
            $changes = array('state' => self::STATE_QUEUED, 'code' => '', 'queuedAt' => time());
            if ($isAutomatic) {
                $changes['autoResumes'] = intval(@$details['job']['autoResumes']) + 1;
            }
            self::saveJob($log, $details, $changes, Publisher_video_publisher_logs::STATUS_PROCESSING);
        } finally {
            if ($lock) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
        if (!self::startWorker($logs_id)) {
            $response['msg'] = self::getMessage('backgroundDisabled');
            return $response;
        }
        $response['error'] = false;
        $response['msg'] = __('The upload was resumed.', true);
        return $response;
    }

    public static function cancel($logs_id, $users_id)
    {
        $response = array('error' => true, 'msg' => '');
        $log = new Publisher_video_publisher_logs($logs_id, true);
        if (empty($log->getId()) || $log->getUsers_id() !== intval($users_id)) {
            $response['msg'] = __('Upload not found', true);
            return $response;
        }
        $details = self::loadDetails($log);
        if (empty($details['job'])) {
            $response['msg'] = __('This upload cannot be canceled.', true);
            return $response;
        }
        $running = self::isWorkerRunning($logs_id);
        $public = self::getPublicJob($details['job'], $running);
        if (!$public['canCancel']) {
            $response['msg'] = __('This upload cannot be canceled.', true);
            return $response;
        }
        if ($running !== false) {
            // the worker checks this file between chunks and stops by itself
            file_put_contents(self::getCancelFile($logs_id), time());
            @chmod(self::getCancelFile($logs_id), 0666);
        }
        if ($running !== true) {
            self::saveJob($log, $details, array('state' => self::STATE_CANCELED, 'code' => '', 'resumeUri' => null), Publisher_video_publisher_logs::STATUS_INACTIVE);
        }
        $response['error'] = false;
        $response['msg'] = __('The upload was canceled.', true);
        return $response;
    }

    /**
     * Cron: restarts uploads whose worker died (server restart, killed process), a few times at most.
     */
    public static function recoverInterruptedJobs()
    {
        $sql = "SELECT pvpl.id, pvpl.users_id, pvpl.details FROM publisher_video_publisher_logs pvpl LEFT JOIN publisher_social_medias psm ON pvpl.publisher_social_medias_id = psm.id"
            . " WHERE pvpl.status = ? AND psm.name = ? AND pvpl.modified > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY pvpl.id ASC LIMIT 20";
        $res = sqlDAL::readSql($sql, 'ss', array(Publisher_video_publisher_logs::STATUS_PROCESSING, SocialMediaPublisher::SOCIAL_TYPE_YOUTUBE['name']), true);
        $rows = $res ? sqlDAL::fetchAllAssoc($res) : array();
        sqlDAL::close($res);
        foreach ($rows as $row) {
            $job = self::getJobFromDetails($row['details']);
            if (empty($job) || !empty($job['legacy'])) {
                continue;
            }
            $public = self::getPublicJob($job, self::isWorkerRunning($row['id']));
            if ($public['state'] !== self::STATE_INTERRUPTED || !$public['canResume']) {
                continue;
            }
            if (intval(@$job['autoResumes']) >= self::MAX_AUTO_RESUMES) {
                continue;
            }
            _error_log("YouTubePublisher::recoverInterruptedJobs resuming logs_id={$row['id']}");
            self::resume($row['id'], $row['users_id'], true);
        }
    }

    /* ------------------------------------------------------------------
     * Worker
     * ---------------------------------------------------------------- */

    /**
     * Finds a file YouTube can take: the best MP4 (remote storage is downloaded by
     * getExistingVideoFile()) or, for HLS-only videos, a temporary MP4 made by m3u8ToMP4().
     * @return array ['path' => string, 'temporary' => bool]
     * @throws YouTubePublisherException
     */
    public static function resolveSourceFile($videos_id)
    {
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        if (empty($filename)) {
            throw new YouTubePublisherException('sourceMissing', 'empty filename');
        }
        $unsupported = self::getUnsupportedTypeCode($video->getType());
        if (!empty($unsupported)) {
            throw new YouTubePublisherException($unsupported, 'type=' . $video->getType());
        }

        $path = $video->getExistingVideoFile();
        if (!empty($path) && file_exists($path) && filesize($path) > 1024) {
            $tmpDir = getTmpDir('getExistingVideoFile');
            return array('path' => $path, 'temporary' => !empty($tmpDir) && strpos($path, $tmpDir) === 0);
        }

        $paths = Video::getVideosPaths($filename, true);
        $m3u8 = '';
        if (!empty($paths['m3u8']['path']) && is_string($paths['m3u8']['path'])) {
            $m3u8 = $paths['m3u8']['path'];
        } elseif (!empty($paths['m3u8']['url']) && is_string($paths['m3u8']['url'])) {
            $m3u8 = $paths['m3u8']['url'];
        }
        if (empty($m3u8)) {
            throw new YouTubePublisherException('sourceMissing', "no mp4 or m3u8 for {$filename}");
        }
        // m3u8ToMP4() allows one conversion at a time on the server; wait for a busy one
        for ($try = 0; $try < 40; $try++) {
            $result = m3u8ToMP4($m3u8);
            if (empty($result['error']) && !empty($result['path']) && file_exists($result['path']) && filesize($result['path']) > 1024) {
                return array('path' => $result['path'], 'temporary' => false);
            }
            $msg = isset($result['msg']) ? (string) $result['msg'] : '';
            if (stripos($msg, 'already running') === false) {
                throw new YouTubePublisherException('notConverted', 'm3u8ToMP4 failed: ' . _substr($msg, 0, 500));
            }
            sleep(15);
        }
        throw new YouTubePublisherException('notConverted', 'm3u8ToMP4 stayed busy');
    }

    /**
     * Starts the resumable session with the authorized HTTP client so that the error body (quota,
     * no channel...) is kept; Google\Http\MediaFileUpload only reports the HTTP code.
     */
    private static function startResumableSession($client, $media, $fileSize)
    {
        $request = $media->getRequest();
        $body = (string) $request->getBody();
        $request = $request
            ->withHeader('content-type', 'application/json; charset=UTF-8')
            ->withHeader('content-length', (string) strlen($body))
            ->withHeader('x-upload-content-type', 'video/*')
            ->withHeader('x-upload-content-length', (string) $fileSize);
        $response = $client->authorize()->send($request, array('http_errors' => false));
        $location = $response->getHeaderLine('location');
        if ($response->getStatusCode() == 200 && !empty($location)) {
            return $location;
        }
        $responseBody = (string) $response->getBody();
        $errors = array();
        $json = json_decode($responseBody, true);
        if (!empty($json['error']['errors']) && is_array($json['error']['errors'])) {
            $errors = $json['error']['errors'];
        }
        throw new Google_Service_Exception($responseBody, $response->getStatusCode(), null, $errors);
    }

    private static function newMediaUpload($client, $youtube, array $metadata, $fileSize)
    {
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($metadata['title']);
        $snippet->setDescription($metadata['description']);
        if (!empty($metadata['tags'])) {
            $snippet->setTags($metadata['tags']);
        }
        if (!empty($metadata['categoryId'])) {
            $snippet->setCategoryId($metadata['categoryId']);
        }
        $status = new Google_Service_YouTube_VideoStatus();
        $status->setPrivacyStatus($metadata['privacy']);

        $video = new Google_Service_YouTube_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        $client->setDefer(true);
        $insertRequest = $youtube->videos->insert('snippet,status', $video);
        $client->setDefer(false);
        $media = new Google_Http_MediaFileUpload($client, $insertRequest, 'video/*', null, true, self::CHUNK_SIZE);
        $media->setFileSize($fileSize);
        return $media;
    }

    /**
     * Classifies an exception thrown while talking to the upload endpoint.
     * @return array ['kind' => auth|expired|transient|fatal, 'code' => message code, 'detail' => string]
     */
    public static function classifyUploadException(\Throwable $e)
    {
        if ($e instanceof YouTubePublisherException) {
            return array('kind' => 'fatal', 'code' => $e->getErrorCode(), 'detail' => $e->getMessage());
        }
        if ($e instanceof \GuzzleHttp\Exception\TransferException && !($e instanceof \GuzzleHttp\Exception\BadResponseException)) {
            return array('kind' => 'transient', 'code' => 'network', 'detail' => get_class($e) . ': ' . $e->getMessage());
        }
        $parsed = self::parseGoogleError($e);
        $detail = "HTTP {$parsed['httpCode']} {$parsed['reason']}";
        if (in_array($parsed['httpCode'], array(404, 410), true)) {
            return array('kind' => 'expired', 'code' => 'network', 'detail' => $detail);
        }
        if ($parsed['httpCode'] === 401) {
            return array('kind' => 'auth', 'code' => 'reconnect', 'detail' => $detail);
        }
        $code = self::classifyGoogleError($parsed['httpCode'], $parsed['reason']);
        if (self::isTransientHttpCode($parsed['httpCode']) && !in_array($code, array('quota', 'channelLimit'), true)) {
            return array('kind' => 'transient', 'code' => $code === 'rateLimit' ? 'rateLimit' : 'network', 'detail' => $detail);
        }
        if (empty($parsed['httpCode']) && $e instanceof \RuntimeException) {
            // not an API answer (stream/IO error)
            return array('kind' => 'transient', 'code' => 'network', 'detail' => get_class($e) . ': ' . _substr($e->getMessage(), 0, 300));
        }
        return array('kind' => 'fatal', 'code' => $code, 'detail' => $detail);
    }

    public static function backoffSeconds($failures)
    {
        return min(64, pow(2, max(0, $failures - 1))) + mt_rand(0, 1000) / 1000;
    }

    /**
     * Resumable upload of an open file. Retries transient errors with exponential backoff, renews
     * the token on 401, restarts once when the session expired, and resumes $resumeUri when given.
     * @param array $hooks onSession($uri), onProgress($bytes), isCanceled(): bool,
     *                     getToken($force): ?string (null = keep the current one), log($msg), sleep($seconds)
     * @return string the YouTube video id
     * @throws YouTubePublisherException (isResumable() when it can continue later)
     */
    public static function uploadFile($client, array $metadata, $handle, $fileSize, $resumeUri, array $hooks = array())
    {
        $call = function ($name) use ($hooks) {
            $args = array_slice(func_get_args(), 1);
            return isset($hooks[$name]) ? call_user_func_array($hooks[$name], $args) : null;
        };
        $youtube = new Google_Service_YouTube($client);
        $media = empty($resumeUri) ? null : self::newMediaUpload($client, $youtube, $metadata, $fileSize);
        $resumeUri = (string) $resumeUri;
        $needsSync = true;
        $result = false;
        $failures = 0;
        $authRetried = false;
        $restarted = false;
        while (true) {
            try {
                if ($resumeUri === '') {
                    $media = self::newMediaUpload($client, $youtube, $metadata, $fileSize);
                    $resumeUri = self::startResumableSession($client, $media, $fileSize);
                    $call('onSession', $resumeUri);
                    $needsSync = true;
                }
                if ($needsSync) {
                    // asks YouTube how many bytes it already has (also completes a finished upload)
                    $result = $media->resume($resumeUri);
                    $needsSync = false;
                }
                if ($result !== false) {
                    break;
                }
                if ($call('isCanceled')) {
                    throw new YouTubePublisherException(self::STATE_CANCELED, 'canceled by the user');
                }
                $token = $call('getToken', false);
                if (!empty($token)) {
                    $client->setAccessToken(array('access_token' => $token));
                }
                $progress = $media->getProgress();
                fseek($handle, $progress);
                $chunk = fread($handle, self::CHUNK_SIZE);
                if ($chunk === false || ($chunk === '' && $progress < $fileSize)) {
                    throw new YouTubePublisherException('sourceMissing', "cannot read the source file at byte {$progress}");
                }
                $result = $media->nextChunk($chunk);
                $failures = 0;
                $authRetried = false;
                $call('onProgress', $result === false ? $media->getProgress() : $fileSize);
            } catch (YouTubePublisherException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $error = self::classifyUploadException($e);
                $call('log', "{$error['kind']} {$error['detail']}");
                if ($error['kind'] === 'auth' && !$authRetried) {
                    $authRetried = true;
                    $token = $call('getToken', true);
                    if (empty($token)) {
                        throw new YouTubePublisherException('reconnect', $error['detail']);
                    }
                    $client->setAccessToken(array('access_token' => $token));
                    $needsSync = true;
                    continue;
                }
                if ($error['kind'] === 'expired' && !$restarted) {
                    // the upload session expired: start again from byte 0
                    $restarted = true;
                    $resumeUri = '';
                    continue;
                }
                if ($error['kind'] === 'transient' && ++$failures <= self::MAX_TRANSIENT_RETRIES) {
                    $seconds = (int) ceil(self::backoffSeconds($failures));
                    if (isset($hooks['sleep'])) {
                        $call('sleep', $seconds);
                    } else {
                        sleep($seconds);
                    }
                    $needsSync = true;
                    continue;
                }
                if ($error['kind'] === 'transient' || $error['kind'] === 'expired') {
                    throw new YouTubePublisherException($error['code'], $error['detail'], true);
                }
                if ($error['kind'] === 'auth') {
                    throw new YouTubePublisherException('reconnect', $error['detail']);
                }
                throw new YouTubePublisherException($error['code'], $error['detail']);
            }
        }
        $youtubeId = is_object($result) && method_exists($result, 'getId') ? $result->getId() : (is_array($result) && !empty($result['id']) ? $result['id'] : '');
        if (empty($youtubeId)) {
            throw new YouTubePublisherException('unknown', 'upload finished without a video id');
        }
        return $youtubeId;
    }

    /**
     * Runs one job. Called only by youtubeUploadWorker.php (CLI).
     */
    public static function runWorker($logs_id)
    {
        $logs_id = intval($logs_id);
        $lock = self::openLock('worker_' . $logs_id);
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            _error_log("YouTubePublisher::runWorker logs_id={$logs_id} is already running or the lock cannot be opened");
            return false;
        }
        $log = new Publisher_video_publisher_logs($logs_id, true);
        $details = self::loadDetails($log);
        $job = isset($details['job']) ? $details['job'] : array();
        if (empty($log->getId()) || empty($job) || !in_array($job['state'], array(self::STATE_QUEUED, self::STATE_PREPARING, self::STATE_UPLOADING), true)) {
            _error_log("YouTubePublisher::runWorker logs_id={$logs_id} nothing to do state=" . (isset($job['state']) ? $job['state'] : ''));
            flock($lock, LOCK_UN);
            fclose($lock);
            return false;
        }
        $cancelFile = self::getCancelFile($logs_id);
        @unlink($cancelFile);
        $users_id = $log->getUsers_id();
        $source = null;
        $handle = null;
        try {
            $details = self::saveJob($log, $details, array('state' => self::STATE_PREPARING, 'code' => '', 'startedAt' => time(), 'attempts' => intval(@$job['attempts']) + 1));
            $source = self::resolveSourceFile($log->getVideos_id());
            $fileSize = filesize($source['path']);
            $handle = fopen($source['path'], 'rb');
            if (!$handle) {
                throw new YouTubePublisherException('sourceMissing', 'cannot open the source file');
            }

            $pubId = intval($details['publisher_user_preferences_id']);
            $client = self::createClient(self::getAccessToken($pubId));
            $resumeUri = '';
            if (!empty($details['job']['resumeUri']) && intval(@$details['job']['totalBytes']) === $fileSize) {
                // same file as the interrupted attempt: continue the same YouTube upload session
                $resumeUri = $details['job']['resumeUri'];
            }
            $youtubeId = self::uploadFile($client, $details['job']['metadata'], $handle, $fileSize, $resumeUri, array(
                'onSession' => function ($uri) use (&$details, $log, $fileSize) {
                    $details = self::saveJob($log, $details, array('state' => self::STATE_UPLOADING, 'resumeUri' => $uri, 'totalBytes' => $fileSize, 'uploadedBytes' => 0));
                },
                'onProgress' => function ($bytes) use (&$details, $log, $fileSize) {
                    $details = self::saveJob($log, $details, array('state' => self::STATE_UPLOADING, 'uploadedBytes' => $bytes, 'totalBytes' => $fileSize));
                },
                'isCanceled' => function () use ($cancelFile) {
                    return file_exists($cancelFile);
                },
                'getToken' => function ($force) use ($pubId) {
                    if (!$force) {
                        $pref = Publisher_user_preferences::getFromDb($pubId, true);
                        $expiresAt = empty($pref['json']) ? 0 : self::getTokenExpiresAt($pref['json']);
                        if (!self::tokenNeedsRefresh($expiresAt, time(), 120)) {
                            return null;
                        }
                    }
                    // a long upload can outlive the one hour access token
                    return self::getAccessToken($pubId, $force);
                },
                'log' => function ($msg) use ($logs_id) {
                    _error_log("YouTubePublisher::runWorker logs_id={$logs_id} {$msg}", AVideoLog::$WARNING);
                },
            ));
            $url = 'https://youtu.be/' . rawurlencode($youtubeId);
            $details['response'] = array('id' => $youtubeId);
            self::saveJob($log, $details, array(
                'state' => self::STATE_PROCESSING,
                'code' => '',
                'youtubeId' => $youtubeId,
                'url' => $url,
                'uploadedBytes' => $fileSize,
                'totalBytes' => $fileSize,
                'finishedAt' => time(),
                'resumeUri' => null,
            ), Publisher_video_publisher_logs::STATUS_VERIFIED);
            _error_log("YouTubePublisher::runWorker logs_id={$logs_id} uploaded youtubeId={$youtubeId} bytes={$fileSize}");
            self::notify($users_id, false, __('Your video was uploaded to YouTube.', true) . ' ' . $url);
        } catch (YouTubePublisherException $e) {
            $log = new Publisher_video_publisher_logs($logs_id, true);
            $details = self::loadDetails($log);
            if ($e->getErrorCode() === self::STATE_CANCELED) {
                self::saveJob($log, $details, array('state' => self::STATE_CANCELED, 'code' => '', 'resumeUri' => null), Publisher_video_publisher_logs::STATUS_INACTIVE);
                _error_log("YouTubePublisher::runWorker logs_id={$logs_id} canceled");
            } elseif ($e->isResumable()) {
                // keeps the resume URI and STATUS_PROCESSING so it can be resumed (manually or by the cron)
                self::saveJob($log, $details, array('state' => self::STATE_INTERRUPTED, 'code' => $e->getErrorCode()));
                _error_log("YouTubePublisher::runWorker logs_id={$logs_id} interrupted {$e->getErrorCode()} {$e->getMessage()}", AVideoLog::$WARNING);
                self::notify($users_id, true, self::getMessage($e->getErrorCode()));
            } else {
                $changes = array('state' => self::STATE_FAILED, 'code' => $e->getErrorCode());
                if (!in_array($e->getErrorCode(), self::RETRYABLE_CODES, true)) {
                    $changes['resumeUri'] = null;
                }
                self::saveJob($log, $details, $changes, Publisher_video_publisher_logs::STATUS_INACTIVE);
                _error_log("YouTubePublisher::runWorker logs_id={$logs_id} failed {$e->getErrorCode()} {$e->getMessage()}", AVideoLog::$ERROR);
                self::notify($users_id, true, self::getMessage($e->getErrorCode()));
            }
        } catch (\Throwable $e) {
            _error_log("YouTubePublisher::runWorker logs_id={$logs_id} unexpected " . get_class($e) . ': ' . $e->getMessage(), AVideoLog::$ERROR);
            $log = new Publisher_video_publisher_logs($logs_id, true);
            self::saveJob($log, self::loadDetails($log), array('state' => self::STATE_FAILED, 'code' => 'unknown'), Publisher_video_publisher_logs::STATUS_INACTIVE);
            self::notify($users_id, true, self::getMessage('unknown'));
        } finally {
            if ($handle) {
                fclose($handle);
            }
            if (!empty($source['temporary']) && file_exists($source['path'])) {
                @unlink($source['path']);
            }
            @unlink($cancelFile);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return true;
    }

    private static function notify($users_id, $isError, $msg)
    {
        try {
            if (function_exists('sendSocketMessageToUsers_id')) {
                sendSocketMessageToUsers_id($msg, $users_id, $isError ? 'avideoToastError' : 'avideoToastSuccess');
            }
        } catch (\Throwable $th) {
            _error_log('YouTubePublisher::notify ' . $th->getMessage());
        }
    }

    /* ------------------------------------------------------------------
     * After the upload
     * ---------------------------------------------------------------- */

    /**
     * Asks YouTube whether an uploaded video finished processing (throttled, 1 quota unit).
     * @return array the job, updated when YouTube answered
     */
    public static function refreshProcessingStatus($logs_id)
    {
        $log = new Publisher_video_publisher_logs($logs_id, true);
        $details = self::loadDetails($log);
        $job = isset($details['job']) ? $details['job'] : array();
        if (empty($job) || $job['state'] !== self::STATE_PROCESSING || empty($job['youtubeId'])) {
            return $job;
        }
        $now = time();
        if (!empty($job['lastProcessingCheck']) && ($now - intval($job['lastProcessingCheck'])) < self::PROCESSING_CHECK_INTERVAL) {
            return $job;
        }
        if (!empty($job['finishedAt']) && ($now - intval($job['finishedAt'])) > self::PROCESSING_CHECK_MAX_AGE) {
            return $job;
        }
        $details = self::saveJob($log, $details, array('lastProcessingCheck' => $now));
        try {
            $client = self::createClient(self::getAccessToken($details['publisher_user_preferences_id']));
            $youtube = new Google_Service_YouTube($client);
            $list = $youtube->videos->listVideos('status', array('id' => $job['youtubeId']));
            $items = $list->getItems();
            if (empty($items)) {
                $result = array('state' => self::STATE_FAILED, 'code' => 'deletedOnYouTube', 'detail' => 'not found');
            } else {
                $result = self::processingResult(json_decode(json_encode($items[0]->toSimpleObject()), true));
            }
            if ($result['state'] !== self::STATE_PROCESSING) {
                $status = $result['state'] === self::STATE_PUBLISHED ? Publisher_video_publisher_logs::STATUS_VERIFIED : Publisher_video_publisher_logs::STATUS_INACTIVE;
                if ($result['state'] !== self::STATE_PUBLISHED) {
                    _error_log("YouTubePublisher::refreshProcessingStatus logs_id={$logs_id} {$result['code']} {$result['detail']}", AVideoLog::$WARNING);
                }
                $details = self::saveJob($log, $details, array('state' => $result['state'], 'code' => $result['code']), $status);
            }
        } catch (\Throwable $e) {
            // the upload itself worked; a failed status check only delays the update
            _error_log("YouTubePublisher::refreshProcessingStatus logs_id={$logs_id} " . get_class($e) . ' ' . _substr($e->getMessage(), 0, 300), AVideoLog::$WARNING);
        }
        return $details['job'];
    }

    /**
     * Assignable YouTube categories in the user's language, cached for a day.
     */
    public static function getCategories($publisher_user_preferences_id, $language = 'en')
    {
        $language = preg_replace('/[^a-zA-Z_-]/', '', (string) $language);
        $hl = strtolower(substr(str_replace('-', '_', $language), 0, 2));
        $region = 'US';
        if (preg_match('/^[a-z]{2}[_-]([a-z]{2})$/i', $language, $matches)) {
            $region = strtoupper($matches[1]);
        }
        $cacheName = "YouTubePublisherCategories_{$hl}_{$region}";
        $cache = ObjectYPT::getCache($cacheName, 86400);
        if (!empty($cache)) {
            return object_to_array($cache);
        }
        $client = self::createClient(self::getAccessToken($publisher_user_preferences_id));
        $youtube = new Google_Service_YouTube($client);
        $list = $youtube->videoCategories->listVideoCategories('snippet', array('regionCode' => $region, 'hl' => empty($hl) ? 'en' : $hl));
        $categories = array();
        foreach ($list->getItems() as $item) {
            $snippet = $item->getSnippet();
            if (!empty($snippet) && $snippet->getAssignable()) {
                $categories[] = array('id' => $item->getId(), 'title' => $snippet->getTitle());
            }
        }
        if (!empty($categories)) {
            ObjectYPT::setCache($cacheName, $categories);
        }
        return $categories;
    }
}

class YouTubePublisherException extends Exception
{
    private $errorCode;
    private $resumable;

    public function __construct($errorCode, $detail = '', $resumable = false)
    {
        parent::__construct((string) $detail);
        $this->errorCode = (string) $errorCode;
        $this->resumable = (bool) $resumable;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function isResumable()
    {
        return $this->resumable;
    }
}

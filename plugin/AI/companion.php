<?php

/**
 * Server-to-server client for the Companion AI-tutor-chat integration.
 * Reuses the SAME marketplace AccessToken already configured above (for
 * ai.ypt.me) to also cover Companion's video processing + chat costs - see
 * https://github.com/WWBN/AVideo/wiki/AI-Plugin (Companion Chat section).
 *
 * Connection is fully automatic: the first call made from any of the
 * methods below will silently call the Companion "connect" bootstrap
 * endpoint and store the returned CompanionApiToken/CompanionOrganizationId/
 * CompanionSiteId in this plugin's own config - the admin never needs to
 * visit Companion's own site.
 */
class CompanionAI
{
    private static $connectionError = '';

    static function getConnectionError()
    {
        return self::$connectionError ?: __('Could not verify the Companion connection or Marketplace balance. Please try again.');
    }

    static function connectionErrorMessage($httpCode, $hasAccessToken)
    {
        if ($httpCode === 402) {
            return $hasAccessToken
                ? __('Add credits to your Marketplace wallet. Creating a Companion organization and site requires a positive available balance, excluding reserved funds. Then try again.')
                : __('Save your Marketplace AccessToken in Admin > Plugins > AI and add credits to that wallet before connecting Companion.');
        }
        if ($httpCode === 400 || $httpCode === 401 || $httpCode === 403) {
            return __('Could not authenticate the connection. Ask your administrator to check the Marketplace AccessToken and the AVideo API credentials.');
        }
        if ($httpCode === 409) {
            return __('This installation or wallet is already linked to another organization. Contact the platform administrator to review the connection.');
        }
        if ($httpCode === 429) {
            return __('Too many connection attempts. Wait a minute and try again.');
        }
        return __('Could not verify the Companion connection or Marketplace balance. Please try again.');
    }

    static function getBaseUrl()
    {
        global $global;
        if (!AVideoPlugin::isEnabledByName('AI')) {
            return '';
        }
        if (!empty($global['companionBaseUrl'])) {
            // Escape hatch for a dev/staging host that is not AI::TEST_DOMAIN.
            $baseUrl = $global['companionBaseUrl'];
        } else {
            $baseUrl = AI::isTestEnvironment() ? AI::COMPANION_LOCAL_BASE_URL : AI::COMPANION_BASE_URL;
        }
        // Fail closed on a malformed override: an unusable base URL makes
        // isConfigured() false (the tab then says the service is unavailable)
        // instead of every request silently timing out against something that
        // is not Companion.
        if (!is_string($baseUrl) || !preg_match('~^https?://~i', $baseUrl) || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return '';
        }
        // Normalized with a single trailing slash so isValidEmbedUrl() below
        // can rely on a stable prefix.
        return rtrim($baseUrl, '/') . '/';
    }

    static function isConfigured()
    {
        return !empty(self::getBaseUrl());
    }

    // The browser-facing UI can have a different origin from the internal API
    // (notably Docker's host.docker.internal:8000 versus localhost:3100).
    static function getFrontendBaseUrl()
    {
        global $global;
        $url = !empty($global['companionFrontendBaseUrl']) ? $global['companionFrontendBaseUrl']
            : (AI::isTestEnvironment() ? 'http://localhost:3100/' : self::getBaseUrl());
        if (!is_string($url) || !preg_match('~^https?://~i', $url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        return rtrim($url, '/') . '/';
    }

    // The widget URL is rendered as an <iframe src> on the public watch page,
    // but it is read back from videos.externalOptions - and any user who can
    // edit a video writes that column directly, because
    // objects/videoAddNew.json.php merges arbitrary $_POST['externalOptions']
    // keys into it. Only accept a URL Companion itself could have minted, so
    // an uploader cannot frame arbitrary content (or a javascript: URI) over
    // every viewer's watch page.
    static function isValidEmbedUrl($embedUrl)
    {
        $baseUrl = self::getFrontendBaseUrl();
        if (empty($baseUrl) || empty($embedUrl) || !is_string($embedUrl)) {
            return false;
        }
        // Keep uploader-controlled externalOptions scoped to the configured
        // UI's widget route, never an arbitrary destination or protocol.
        return strncasecmp($embedUrl, $baseUrl, strlen($baseUrl)) === 0
            && preg_match('~^widget/[A-Za-z0-9_.-]+$~D', substr($embedUrl, strlen($baseUrl)));
    }

    // Public launcher branding JSON for the widget (agent name/avatar,
    // invitation text). Same trust problem as the embed URL: it is read back
    // from uploader-writable externalOptions and fetched by every viewer's
    // browser, so only accept Companion's own public widget route on a host
    // that belongs to the configured Companion API or UI.
    static function isValidConfigUrl($configUrl)
    {
        if (empty($configUrl) || !is_string($configUrl) || !preg_match('~^https?://~i', $configUrl)) {
            return false;
        }
        $parts = parse_url($configUrl);
        if (empty($parts['host']) || empty($parts['path']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || !empty($parts['query'])) {
            return false;
        }
        if (!preg_match('~^/api/v1/public/widget/[A-Za-z0-9_.-]+$~D', $parts['path'])) {
            return false;
        }
        $allowedHosts = array();
        foreach (array(self::getBaseUrl(), self::getFrontendBaseUrl()) as $base) {
            $host = empty($base) ? '' : parse_url($base, PHP_URL_HOST);
            if (!empty($host)) {
                $allowedHosts[] = strtolower($host);
            }
        }
        // Inside Docker the plugin reaches Companion through host.docker.internal
        // while browsers use localhost; both name the same local API.
        if (AI::isTestEnvironment()) {
            $allowedHosts[] = 'localhost';
            $allowedHosts[] = '127.0.0.1';
        }
        return in_array(strtolower($parts['host']), $allowedHosts, true);
    }

    // Older enabled videos only stored embedUrl. Resolve their branding URL
    // without a database write, a new enable request or a Companion round-trip.
    static function getWidgetConfigUrl($embedUrl, $configUrl = '')
    {
        if (!self::isValidEmbedUrl($embedUrl)) {
            return '';
        }
        if (self::isValidConfigUrl($configUrl)) {
            return $configUrl;
        }
        $baseUrl = self::getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }
        // PHP uses Docker's host alias; a local viewer uses localhost.
        if (AI::isTestEnvironment() && parse_url($baseUrl, PHP_URL_HOST) === 'host.docker.internal') {
            $baseUrl = str_replace('://host.docker.internal', '://localhost', $baseUrl);
        }
        $key = substr($embedUrl, strlen(self::getFrontendBaseUrl() . 'widget/'));
        return $baseUrl . 'api/v1/public/widget/' . $key;
    }

    private static function request($method, $path, $params = array(), $headers = array())
    {
        $baseUrl = self::getBaseUrl();
        if (empty($baseUrl)) {
            return null;
        }
        $url = $baseUrl . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        // An unreachable host must fail in seconds: the chat tab polls this
        // path every 5s while a video is processing, and each poll can chain
        // up to three requests.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(array('Content-Type: application/json'), $headers));
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            _error_log("[CompanionAI] request to {$url} failed: {$error}", AVideoLog::$ERROR);
            return null;
        }
        return array('httpCode' => $httpCode, 'body' => json_decode($response));
    }

    static function getIntegrationKey()
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled('AI');
        return empty($obj->CompanionApiToken) ? '' : $obj->CompanionApiToken;
    }

    private static function authHeader()
    {
        return array('X-Avideo-Integration-Key: ' . self::getIntegrationKey());
    }

    private static function saveConnection($body, $fingerprint)
    {
        $plugin = AVideoPlugin::loadPlugin('AI');
        if (!is_object($plugin)) {
            return false;
        }
        // Never persist a non-scalar/empty credential: this row is the whole
        // plugin config, and a malformed response would leave the plugin in a
        // state no admin can read or repair from the UI.
        foreach (array('integration_api_key', 'organization_id', 'site_id') as $field) {
            if (empty($body->{$field}) || !is_scalar($body->{$field})) {
                _error_log("[CompanionAI] connect response is missing {$field}", AVideoLog::$ERROR);
                return false;
            }
        }
        // Save together: individual setters reload cached data and overwrite prior fields.
        $object = AVideoPlugin::getObjectDataIfEnabled('AI');
        $object->CompanionApiToken = (string) $body->integration_api_key;
        $object->CompanionOrganizationId = (string) $body->organization_id;
        $object->CompanionSiteId = (string) $body->site_id;
        $object->CompanionConnectionFingerprint = $fingerprint;
        return $plugin->setDataObject($object);
    }

    private static function getSiteApiSecret()
    {
        $api = AVideoPlugin::getObjectDataIfEnabled('API');
        return empty($api->APISecret) ? '' : $api->APISecret;
    }

    private static function connectionFingerprint($accessToken)
    {
        global $global;
        return hash('sha256', json_encode(array('site-identity-v3', self::getBaseUrl(), $global['webSiteRootURL'], $accessToken, self::getSiteApiSecret())));
    }

    private static function connect($accessToken)
    {
        global $global;
        self::$connectionError = '';
        $params = array(
            'avideo_base_url' => $global['webSiteRootURL'],
            'avideo_site_name' => parse_url($global['webSiteRootURL'], PHP_URL_HOST),
        );
        $siteSecret = self::getSiteApiSecret();
        if ($siteSecret !== '') {
            $params['avideo_api_secret'] = $siteSecret;
        }
        if (!empty($accessToken)) {
            $params['marketplace_access_token'] = $accessToken;
        }
        $res = self::request('POST', 'api/v1/integrations/avideo/connect', $params, self::authHeader());
        if (empty($res) || $res['httpCode'] != 201 || empty($res['body']->integration_api_key)) {
            self::$connectionError = self::connectionErrorMessage(empty($res) ? 0 : (int) $res['httpCode'], !empty($accessToken));
            _error_log('[CompanionAI] connect failed, HTTP ' . (empty($res) ? 0 : $res['httpCode']), AVideoLog::$ERROR);
            return false;
        }
        return !empty(self::saveConnection($res['body'], self::connectionFingerprint($accessToken)));
    }

    // Re-resolve the wallet identity when credentials or the installation change.
    static function ensureConnected()
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled('AI');
        if (empty($obj) || !self::isConfigured()) {
            return false;
        }
        $accessToken = empty($obj->AccessToken) ? '' : $obj->AccessToken;
        if (!empty($obj->CompanionApiToken) && !empty($obj->CompanionConnectionFingerprint)
            && hash_equals($obj->CompanionConnectionFingerprint, self::connectionFingerprint($accessToken))) {
            return true;
        }
        return self::connect($accessToken);
    }

    static function linkMarketplace($accessToken)
    {
        if (empty($accessToken) || !self::connect($accessToken)) {
            return null;
        }
        return self::status();
    }

    static function status()
    {
        if (!self::ensureConnected()) {
            return null;
        }
        $res = self::request('GET', 'api/v1/integrations/avideo/status', array(), self::authHeader());
        if (empty($res) || $res['httpCode'] != 200) {
            return null;
        }
        return $res['body'];
    }

    /**
     * One-time link that signs the CURRENT AVideo admin in to Companion's own
     * dashboard for THIS installation's Site (Companion never sees an AVideo
     * password). Only ever called after User::isAdmin() (see
     * companionAdminLogin.json.php); Companion scopes that session to the one
     * Site - nothing of the Organization or other sites is reachable with it.
     * Returns null when the integration is not connected/reachable.
     */
    static function adminLoginUrl($users_id, $displayName)
    {
        if (!self::ensureConnected()) {
            return null;
        }
        $res = self::request('POST', 'api/v1/integrations/avideo/admin-login', array(
            'avideo_users_id' => (string) $users_id,
            'display_name' => (string) $displayName,
        ), self::authHeader());
        if (empty($res) || $res['httpCode'] != 200 || empty($res['body']->login_url)) {
            return null;
        }
        return $res['body']->login_url;
    }

    static function submitVideo($videos_id)
    {
        if (!self::ensureConnected()) {
            return null;
        }
        if (self::isPasswordProtected($videos_id)) {
            // videos.video_password only ever holds a HASH (see
            // Video::setVideo_password()), so there is no plaintext to hand
            // over and Companion could never fetch submitted_url. Fail before
            // the request instead of paying for a processing run that cannot
            // read the video.
            _error_log("[CompanionAI] video {$videos_id} is password protected, not submitting", AVideoLog::$WARNING);
            return null;
        }
        $params = array(
            'avideo_video_id' => (string) $videos_id,
            'submitted_url' => Video::getLinkToVideo($videos_id),
        );
        $res = self::request('POST', 'api/v1/integrations/avideo/videos', $params, self::authHeader());
        if (empty($res) || $res['httpCode'] >= 300) {
            return null;
        }
        return $res['body'];
    }

    static function isPasswordProtected($videos_id)
    {
        $video = new Video('', '', $videos_id);
        return $video->getVideo_password() !== '';
    }

    static function getVideoStatus($videos_id, &$httpCode = null)
    {
        if (!self::ensureConnected()) {
            return null;
        }
        $res = self::request('GET', 'api/v1/integrations/avideo/videos/' . urlencode($videos_id), array(), self::authHeader());
        $httpCode = empty($res) ? 0 : $res['httpCode'];
        if (empty($res) || $res['httpCode'] != 200) {
            return null;
        }
        return $res['body'];
    }

    // Mints (or refreshes) the widget iframe URL for this video and stores
    // it locally in videos.externalOptions, so getFooterCode() below never
    // needs a fresh Companion round-trip on every single page view.
    static function enableChat($videos_id)
    {
        if (!self::ensureConnected()) {
            return null;
        }
        $res = self::request('POST', 'api/v1/integrations/avideo/videos/' . urlencode($videos_id) . '/chat/enable', array(), self::authHeader());
        if (empty($res) || $res['httpCode'] != 200 || empty($res['body']->widget_embed_url)) {
            return null;
        }
        if (!self::isValidEmbedUrl($res['body']->widget_embed_url)) {
            _error_log('[CompanionAI] refused a widget URL outside ' . self::getBaseUrl(), AVideoLog::$SECURITY);
            return null;
        }
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        if (empty($externalOptions)) {
            $externalOptions = new stdClass();
        }
        $configUrl = empty($res['body']->widget_config_url) ? '' : $res['body']->widget_config_url;
        if (!empty($configUrl) && !self::isValidConfigUrl($configUrl)) {
            _error_log('[CompanionAI] ignored a widget config URL outside Companion: ' . $configUrl, AVideoLog::$SECURITY);
            $configUrl = '';
        }
        $externalOptions->companionChat = array(
            'enabled' => true,
            'embedUrl' => $res['body']->widget_embed_url,
            // Launcher branding (agent photo, invitation) is fetched live by
            // the watch page, so a new avatar in Companion shows up without
            // re-enabling the chat.
            'configUrl' => $configUrl,
        );
        $video->setExternalOptions(json_encode($externalOptions));
        $video->save();
        return $res['body'];
    }

    static function disableChat($videos_id)
    {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        if (!empty($externalOptions) && isset($externalOptions->companionChat)) {
            $externalOptions->companionChat->enabled = false;
            $video->setExternalOptions(json_encode($externalOptions));
            $video->save();
        }
    }

    static function getStoredChatConfig($videos_id)
    {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        return empty($externalOptions->companionChat) ? null : $externalOptions->companionChat;
    }

    // --- Subtitles: Companion transcript -> SubtitleSwitcher ----------------
    // AVideo pulls the WebVTT over the same authenticated server-to-server
    // channel; Companion never writes into this installation. SubtitleSwitcher
    // shows every videos/{filename}/{filename}.{lang}.vtt as a player track.

    const SUBTITLE_MAX_BYTES = 5242880;
    // Scheduled imports give up after this; the tab button still works later.
    const SUBTITLE_SCHEDULE_MAX_AGE_SECONDS = 259200;

    static function isSubtitleSwitcherEnabled()
    {
        return AVideoPlugin::isEnabledByName('SubtitleSwitcher') && function_exists('deleteVTTCache');
    }

    static function getSubtitle($videos_id, &$httpCode = null)
    {
        $httpCode = 0;
        if (!self::ensureConnected()) {
            return null;
        }
        $res = self::request('GET', 'api/v1/integrations/avideo/videos/' . urlencode($videos_id) . '/subtitles', array(), self::authHeader());
        $httpCode = empty($res) ? 0 : (int) $res['httpCode'];
        if (empty($res) || $res['httpCode'] != 200 || !is_object($res['body'])) {
            return null;
        }
        return $res['body'];
    }

    // Companion reports an ISO 639 code ("pt"); the file name needs a code
    // SubtitleSwitcher can read ([a-zA-Z_]+), preferably one with a label.
    static function subtitleLanguage($detected)
    {
        global $global, $config;
        if (is_string($detected) && preg_match('/^[a-z]{2,3}$/', $detected)) {
            return $detected;
        }
        // Transcribed before Companion kept the language: use the site's own.
        $siteLanguage = is_object($config) ? $config->getLanguage() : '';
        if ($siteLanguage === 'us') {
            $siteLanguage = 'en';
        }
        if (is_string($siteLanguage) && isset($global['bcp47'][$siteLanguage]) && preg_match('/^[a-zA-Z_]+$/', $siteLanguage)) {
            return $siteLanguage;
        }
        return 'en';
    }

    // Always read the row itself ($refreshCache): a cached copy from earlier
    // in the same request/cron run predates the state this import just saved.
    static function getSubtitleState($videos_id)
    {
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions());
        return empty($externalOptions->companionSubtitle) ? null : $externalOptions->companionSubtitle;
    }

    private static function saveSubtitleState($videos_id, $state)
    {
        // Fresh load: enableChat() writes the same column.
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions());
        if (empty($externalOptions) || !is_object($externalOptions)) {
            $externalOptions = new stdClass();
        }
        $externalOptions->companionSubtitle = $state;
        $video->setExternalOptions(json_encode($externalOptions));
        // allowOfflineUser: AI::executeEveryMinute() runs from cron with no
        // session (same as CDNStorage); web callers already passed canEdit.
        return $video->save(false, true);
    }

    private static function subtitleResult($error, $code, $msg, $extra = array())
    {
        $result = (object) array_merge(array('error' => $error, 'code' => $code, 'msg' => $msg), $extra);
        return $result;
    }

    /**
     * Saves this video's Companion transcript as a SubtitleSwitcher track.
     * Without $overwrite it never replaces a subtitle file for the same
     * language that this import did not write itself (a manual upload or an
     * ai.ypt.me transcription); the result code 'exists' lets the UI ask.
     * $automatic (scheduler / tab) remembers why it skipped, so it does not
     * download the same transcript again on every run.
     */
    static function importSubtitle($videos_id, $overwrite = false, $automatic = false)
    {
        $videos_id = intval($videos_id);
        if (!self::isSubtitleSwitcherEnabled()) {
            return self::subtitleResult(true, 'plugin_disabled', __('Enable the SubtitleSwitcher plugin to show subtitles on the player.'));
        }
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        if (empty($videos_id) || empty($filename) || !preg_match('/^[a-zA-Z0-9_-]+$/', $filename)) {
            return self::subtitleResult(true, 'video_not_found', __('Video not found'));
        }

        $httpCode = 0;
        $subtitle = self::getSubtitle($videos_id, $httpCode);
        if (empty($subtitle)) {
            if ($httpCode === 404) {
                return self::subtitleResult(true, 'not_processed', __('This video has not been processed for Chat yet.'));
            }
            if ($httpCode === 409) {
                return self::subtitleResult(true, 'not_ready', __('The subtitle will be available when processing finishes.'));
            }
            return self::subtitleResult(true, 'unavailable', __('Could not get the subtitle from Companion. Please try again.'));
        }
        return self::storeSubtitle($videos_id, $filename, $subtitle, $overwrite, $automatic);
    }

    // Validates Companion's response and writes it as the video's track
    // (separate from the HTTP fetch so it is testable with a fixture).
    private static function storeSubtitle($videos_id, $filename, $subtitle, $overwrite, $automatic)
    {
        $vtt = isset($subtitle->vtt) && is_string($subtitle->vtt) ? $subtitle->vtt : '';
        $versionId = isset($subtitle->transcript_version_id) && is_scalar($subtitle->transcript_version_id) ? (string) $subtitle->transcript_version_id : '';
        if (empty($subtitle->segment_count) || strpos($vtt, '-->') === false) {
            if ($automatic) {
                self::saveSubtitleState($videos_id, array('skipped' => 'no_speech', 'transcriptVersionId' => $versionId, 'checkedAt' => time()));
            }
            return self::subtitleResult(false, 'no_speech', __('No speech was transcribed in this video, so there is no subtitle to copy.'));
        }
        if (strlen($vtt) > self::SUBTITLE_MAX_BYTES || strncmp($vtt, 'WEBVTT', 6) !== 0 || !mb_check_encoding($vtt, 'UTF-8') || $versionId === '') {
            _error_log("[CompanionAI] refused an invalid subtitle for video {$videos_id}", AVideoLog::$ERROR);
            return self::subtitleResult(true, 'invalid', __('Companion returned an invalid subtitle file.'));
        }

        $lang = self::subtitleLanguage(isset($subtitle->language) ? $subtitle->language : null);
        $vttName = "{$filename}.{$lang}.vtt";
        $path = Video::getPathToFile($vttName, true);
        if (empty($path) || basename($path) !== $vttName) {
            _error_log("[CompanionAI] could not resolve the subtitle path for video {$videos_id}", AVideoLog::$ERROR);
            return self::subtitleResult(true, 'write_failed', __('Could not save the subtitle file.'));
        }

        // externalOptions is writable by anyone who can edit the video, so the
        // stored state is only a hint: every field is type-checked and a file
        // counts as ours only while its bytes still match the recorded hash.
        $state = self::getSubtitleState($videos_id);
        $previousLang = (!empty($state->lang) && is_string($state->lang) && preg_match('/^[a-zA-Z_]+$/', $state->lang)) ? $state->lang : '';
        $previousSha = (!empty($state->sha256) && is_string($state->sha256)) ? $state->sha256 : '';
        $previousVersion = (!empty($state->transcriptVersionId) && is_string($state->transcriptVersionId)) ? $state->transcriptVersionId : '';
        $ownFile = $previousLang === $lang && $previousSha !== ''
            && file_exists($path) && hash_equals($previousSha, (string) hash_file('sha256', $path));
        if (file_exists($path) && !$ownFile && !$overwrite) {
            if ($automatic) {
                self::saveSubtitleState($videos_id, array('skipped' => 'exists', 'lang' => $lang, 'transcriptVersionId' => $versionId, 'checkedAt' => time()));
            }
            return self::subtitleResult(true, 'exists', __('This video already has a subtitle in this language. Replace it with the Companion transcript?'), array('lang' => $lang));
        }
        if ($ownFile && !$overwrite && $previousVersion === $versionId) {
            return self::subtitleResult(false, 'up_to_date', __('The subtitle is already on the player.'), array('lang' => $lang));
        }

        // Write next to the target and rename, so the player never reads a
        // half-written file; the temporary name never matches *.{lang}.vtt.
        $tmp = $path . '.' . uniqid() . '.tmp';
        if (file_put_contents($tmp, $vtt, LOCK_EX) !== strlen($vtt) || !rename($tmp, $path)) {
            @unlink($tmp);
            _error_log("[CompanionAI] could not write {$vttName}", AVideoLog::$ERROR);
            return self::subtitleResult(true, 'write_failed', __('Could not save the subtitle file.'));
        }
        // API/playlists build this .srt once from the .vtt and never refresh it.
        $srtPath = Video::getPathToFile("{$filename}.{$lang}.srt");
        if (!empty($srtPath) && file_exists($srtPath)) {
            @unlink($srtPath);
        }
        // A reprocessing that detected another language must not leave the
        // previous Companion track behind; only a file still byte-identical to
        // what this import wrote is removed.
        if ($previousLang !== '' && $previousLang !== $lang && $previousSha !== '') {
            $previousPath = Video::getPathToFile("{$filename}.{$previousLang}.vtt");
            if (!empty($previousPath) && file_exists($previousPath) && hash_equals($previousSha, (string) hash_file('sha256', $previousPath))) {
                @unlink($previousPath);
            }
        }
        deleteVTTCache($filename);
        ObjectYPT::deleteCache("SubtitleSwitcher_getVideoTags{$videos_id}");
        Video::clearCache($videos_id);

        $state = array(
            'lang' => $lang,
            'detectedLanguage' => isset($subtitle->language) && is_string($subtitle->language) ? $subtitle->language : '',
            'transcriptVersionId' => $versionId,
            'sha256' => hash('sha256', $vtt),
            'importedAt' => time(),
        );
        self::saveSubtitleState($videos_id, $state);
        return self::subtitleResult(false, 'imported', __('The subtitle was copied to the player.'), array('lang' => $lang));
    }

    // Queued on submit and handled by AI::executeEveryMinute(), so the
    // subtitle arrives even when nobody keeps the Video chat tab open.
    static function scheduleSubtitleImport($videos_id, $users_id)
    {
        if (!self::isSubtitleSwitcherEnabled()
            || Ai_scheduler::isAlreadyScheduled($videos_id, Ai_scheduler::$typeCompanionSubtitle, Ai_scheduler::$statusActive)) {
            return false;
        }
        $json = new stdClass();
        $json->videos_id = intval($videos_id);
        $json->users_id = intval($users_id);
        $json->scheduledAt = time();
        $ai = new Ai_scheduler(0);
        $ai->setAi_scheduler_type(Ai_scheduler::$typeCompanionSubtitle);
        $ai->setJson($json);
        $ai->setStatus(Ai_scheduler::$statusActive);
        return $ai->save();
    }

    static function processScheduledSubtitle($row)
    {
        $ai = new Ai_scheduler($row['id']);
        $json = _json_decode($ai->getJson());
        $videos_id = empty($json->videos_id) ? 0 : intval($json->videos_id);
        $expired = empty($json->scheduledAt) || (time() - intval($json->scheduledAt)) > self::SUBTITLE_SCHEDULE_MAX_AGE_SECONDS;

        $finalStatus = null;
        if (empty($videos_id) || !self::isSubtitleSwitcherEnabled()) {
            $finalStatus = Ai_scheduler::$statusError;
        } else {
            $httpCode = 0;
            $videoStatus = self::getVideoStatus($videos_id, $httpCode);
            if (empty($videoStatus)) {
                // 404: Companion never received this video; anything else is
                // a temporary outage, retried until the row expires.
                if ($httpCode === 404 || $expired) {
                    $finalStatus = Ai_scheduler::$statusError;
                }
            } elseif (in_array($videoStatus->status, array('failed', 'cancelled'), true)) {
                $finalStatus = Ai_scheduler::$statusError;
            } elseif ($videoStatus->status === 'ready') {
                $result = self::importSubtitle($videos_id, false, true);
                _error_log("[CompanionAI] scheduled subtitle import for video {$videos_id}: {$result->code}");
                if (!$result->error || $result->code === 'exists') {
                    // Done, including a deliberate skip the tab still offers to override.
                    $finalStatus = Ai_scheduler::$statusExecuted;
                } elseif (in_array($result->code, array('plugin_disabled', 'video_not_found', 'invalid'), true) || $expired) {
                    $finalStatus = Ai_scheduler::$statusError;
                }
            } elseif ($expired) {
                $finalStatus = Ai_scheduler::$statusError;
            }
        }
        if ($finalStatus !== null) {
            $ai->setStatus($finalStatus);
            $ai->save();
        }
        return $finalStatus;
    }

    static function canUseChat()
    {
        if (User::isAdmin() || isCommandLineInterface()) {
            return true;
        }
        return Permissions::hasPermission(AI::PERMISSION_CAN_USE_COMPANION_CHAT, 'AI');
    }

    // Decides whether the CURRENT viewer (on the public watch page) is
    // allowed to see the auto-injected chat widget - deliberately a soft,
    // client-side gate (same "obscurity, not hard access control" model
    // AVideo already uses for unlisted videos), not a hard server-verified
    // proof passed to Companion.
    static function whoCanUseChatOnWatchPage()
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled('AI');
        if (empty($obj)) {
            return false;
        }
        $mode = empty($obj->companionChatAccessMode) ? 'everyone' : $obj->companionChatAccessMode;
        if (is_object($mode) && isset($mode->value)) {
            $mode = $mode->value;
        }
        if (!in_array($mode, array('everyone', 'logged_in', 'usergroup'), true)) {
            $mode = 'everyone';
        }
        switch ($mode) {
            case 'logged_in':
                return User::isLogged();
            case 'usergroup':
                return self::canUseChat();
            default: // 'everyone'
                return true;
        }
    }
}

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
        $externalOptions->companionChat = array(
            'enabled' => true,
            'embedUrl' => $res['body']->widget_embed_url,
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

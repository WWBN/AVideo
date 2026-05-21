<?php

/**
 * It return true in case the $html_string is a string 'false' (good for post/get variables check)
 * It also return true in case it is an empty HTML
 * @param string $html_string
 * @return boolean
 */
function _empty($html_string)
{
    if (empty($html_string)) {
        return true;
    }
    if (is_string($html_string)) {
        if (mb_strtolower($html_string) == 'false') {
            return true;
        }
        if (mb_strtolower($html_string) == 'null') {
            return true;
        }
    }
    return emptyHTML($html_string);
}

function _intval($string)
{
    if (is_string($string)) {
        if (mb_strtolower($string) == 'true') {
            return 1;
        }
    }
    return intval($string);
}


function _strtotime($datetime)
{
    return is_int($datetime) ? $datetime : strtotime($datetime);
}
/**
 * @link https://github.com/php/php-src/issues/8218#issuecomment-1072439915
 */
function _ob_end_clean()
{
    @ob_end_clean();
    header_remove('Content-Encoding');
}

function _ob_clean()
{
    @ob_clean();
    header_remove('Content-Encoding');
}



function _ob_start($force = false)
{
    global $global;
    if (!isset($global['ob_start_callback'])) {
        $global['ob_start_callback'] = 'ob_gzhandler';
    } else {
        if (empty($global['ob_start_callback'])) {
            $global['ob_start_callback'] = null;
        }
    }
    if (!empty($global['ob_start_callback']) && empty($force) && ob_get_level()) {
        return false;
    }
    ob_start($global['ob_start_callback']);
}

/**
 *
  clear  return  send    stop
  ob_clean          x
  ob_end_clean      x                      x
  ob_end_flush                      x      x
  ob_flush                          x
  ob_get_clean      x        x             x  // should be called ob_get_end_clean
  ob_get_contents            x
  ob_get_flush               x      x
 */
function _ob_get_clean()
{
    $content = ob_get_contents();
    _ob_end_clean();
    _ob_start();
    return $content;
}

function _getCookieTargetDomains()
{
    $domain = _normalizeCookieDomain(getDomain());
    $domains = [];

    if (!empty($domain)) {
        $domains[] = $domain;

        $wwwDomain = 'www.' . ltrim($domain, '.');
        if (stripos($domain, 'www.') !== 0 && _normalizeCookieDomain($wwwDomain) !== null) {
            $domains[] = $wwwDomain;
        }
    }

    return array_values(array_unique(array_filter($domains)));
}

function _getCookieDeleteTargets()
{
    $targets = [
        [
            'path' => '/',
            'domain' => null,
        ],
    ];

    foreach (_getCookieTargetDomains() as $domain) {
        $targets[] = [
            'path' => '/',
            'domain' => $domain,
        ];
        $targets[] = [
            'path' => '/',
            'domain' => '.' . ltrim($domain, '.'),
        ];
    }

    return $targets;
}

function _getCookieRequestDomain($host = null)
{
    global $global;

    if ($host === null) {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (!empty($global['webSiteRootURL'])) {
            $host = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        }
    }

    $host = (string) $host;
    $host = preg_replace('/^www\./i', '', $host);
    $host = preg_match('/^\..+/', $host) ? ltrim($host, '.') : $host;
    $host = preg_replace('/:[0-9]+$/', '', $host);

    return $host;
}

function _normalizeCookieDomain($domain)
{
    $domain = strtolower(trim((string) $domain));
    $domain = preg_replace('/^www\./i', '', $domain);
    $domain = preg_match('/^\..+/', $domain) ? ltrim($domain, '.') : $domain;
    $domain = preg_replace('/:[0-9]+$/', '', $domain);
    $domain = trim($domain, '.');

    if ($domain === '') {
        return null;
    }

    $ipDomain = trim($domain, '[]');
    if (filter_var($ipDomain, FILTER_VALIDATE_IP)) {
        return null;
    }

    if (strpos($domain, '.') === false) {
        return null;
    }

    if (strlen($domain) > 253 || !preg_match('/^[a-z0-9.-]+$/', $domain)) {
        return null;
    }

    foreach (explode('.', $domain) as $label) {
        if ($label === '' || strlen($label) > 63 || $label[0] === '-' || substr($label, -1) === '-') {
            return null;
        }
    }

    return $domain;
}

function _isCookieSecure()
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if (!empty($_SERVER['REQUEST_SCHEME']) && strtolower((string) $_SERVER['REQUEST_SCHEME']) === 'https') {
        return true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $forwardedProto = array_map('trim', explode(',', strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO'])));
        if (in_array('https', $forwardedProto, true)) {
            return true;
        }
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
        return true;
    }

    if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower((string) $_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }

    if (!empty($_SERVER['HTTP_CF_VISITOR']) && stripos((string) $_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"') !== false) {
        return true;
    }

    if (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }

    return false;
}

function _getCookiePolicy()
{
    static $policy = null;

    if ($policy !== null) {
        return $policy;
    }

    $secure = _isCookieSecure();
    $policy = [
        'secure' => $secure,
        'httponly' => true,
        'samesite' => _getCookieSameSiteValue($secure),
    ];

    return $policy;
}

function _getDefaultCookieDomain()
{
    $domain = function_exists('getDomain') ? getDomain() : _getCookieRequestDomain();
    return _normalizeCookieDomain($domain);
}

function _getSessionCookieIniSettings()
{
    $policy = _getCookiePolicy();

    return [
        'session.cookie_samesite' => $policy['samesite'],
        'session.cookie_secure' => $policy['secure'] ? '1' : '0',
    ];
}

function _applySessionCookieIniSettings()
{
    foreach (_getSessionCookieIniSettings() as $name => $value) {
        ini_set($name, $value);
    }
}

function _getCookieSameSiteValue($secure)
{
    return $secure ? 'None' : 'Lax';
}

function _getSessionCookieParamsConfig($lifetime, $domain = null, $path = '/')
{
    $policy = _getCookiePolicy();

    return [
        'lifetime' => (int) $lifetime,
        'path' => $path,
        'domain' => _normalizeCookieDomain($domain),
        'secure' => $policy['secure'],
        'httponly' => $policy['httponly'],
        'samesite' => $policy['samesite'],
    ];
}

function _supportsCookieOptionsArray()
{
    return version_compare(PHP_VERSION, '7.3.0', '>=');
}

function _getCookieOptionsArray(array $config, $includeExpires = true)
{
    $cookieOptions = [
        'path' => $config['path'],
        'secure' => $config['secure'],
        'httponly' => $config['httponly'],
        'samesite' => $config['samesite'],
    ];

    if ($includeExpires) {
        $cookieOptions['expires'] = $config['lifetime'];
    }

    $domain = _normalizeCookieDomain($config['domain']);
    if ($domain !== null) {
        $cookieOptions['domain'] = $domain;
    }

    return $cookieOptions;
}

function _getLegacySameSitePath(array $config)
{
    $path = $config['path'];

    if ($config['secure'] || !empty($config['samesite'])) {
        $path .= '; SameSite=' . $config['samesite'];
    }

    return $path;
}

function _setcookieInternal($cookieName, $value, $expires, $path = '/', $domain = null)
{
    $config = _getSessionCookieParamsConfig($expires, $domain, $path);

    if (_supportsCookieOptionsArray()) {
        return setcookie($cookieName, $value, _getCookieOptionsArray($config));
    }

    return setcookie(
        $cookieName,
        $value,
        $config['lifetime'],
        _getLegacySameSitePath($config),
        $config['domain'],
        $config['secure'],
        $config['httponly']
    );
}

function _setSessionCookieParams($lifetime, $domain = null, $path = '/')
{
    if ($domain === null) {
        $domain = _getDefaultCookieDomain();
    }

    $config = _getSessionCookieParamsConfig($lifetime, $domain, $path);

    if (_supportsCookieOptionsArray()) {
        return session_set_cookie_params(array_merge(
            ['lifetime' => $config['lifetime']],
            _getCookieOptionsArray($config, false)
        ));
    }

    return session_set_cookie_params(
        $config['lifetime'],
        _getLegacySameSitePath($config),
        $config['domain'],
        $config['secure'],
        $config['httponly']
    );
}

function _setcookie($cookieName, $value, $expires = 0)
{
    if ($cookieName === 'pass') {
        _error_log('_setcookie pass changed ' . $value);
    }
    global $config, $global;
    if (empty($expires)) {
        if (empty($config) || !is_object($config)) {
            require_once $global['systemRootPath'] . 'objects/configuration.php';
            if (class_exists('AVideoConf')) {
                $config = new AVideoConf();
            }
        }
        if (!empty($config) && is_object($config)) {
            $expires = time() + $config->getSession_timeout();
        }
    }

    // Clear any stale host-only cookie before writing the fresh value. Normal
    // domains use domain-scoped cookies; IP/local hosts fall back to host-only.
    _setcookieInternal($cookieName, '', strtotime('-10 years'), '/', null);

    $set = false;
    $domains = _getCookieTargetDomains();
    if (empty($domains)) {
        $set = _setcookieInternal($cookieName, $value, $expires, '/', null);
    } else {
        foreach ($domains as $domain) {
            $set = _setcookieInternal($cookieName, $value, $expires, '/', $domain) || $set;
        }
    }
    $_COOKIE[$cookieName] = $value;
    return $set;
}

function _unsetcookie($cookieName)
{
    $expires = strtotime("-10 years");
    $value = '';

    foreach (_getCookieDeleteTargets() as $target) {
        _setcookieInternal($cookieName, $value, $expires, $target['path'], $target['domain']);
    }

    setcookie($cookieName, $value, (int) $expires);
    unset($_COOKIE[$cookieName]);
}

function _resetcookie($cookieName, $value)
{
    _unsetcookie($cookieName);
    _setcookie($cookieName, $value);
}

// this will make sure the strring will fits in the database field
function _substr($string, $start, $length = null)
{
    // make sure the name is not chunked in case of multibyte string
    if (function_exists("mb_strcut")) {
        return mb_strcut($string, $start, $length, "UTF-8");
    } else {
        return substr($string, $start, $length);
    }
}

function _strlen($string)
{
    // make sure the name is not chunked in case of multibyte string
    if (function_exists("mb_strlen")) {
        return mb_strlen($string, "UTF-8");
    } else {
        return strlen($string);
    }
}

function is_utf8($string)
{
    return preg_match('//u', $string);
}

function _utf8_encode_recursive($object)
{
    if (is_string($object)) {
        return is_utf8($object) ? $object : utf8_encode($object);
    }

    if (is_array($object)) {
        foreach ($object as $key => $value) {
            $object[$key] = _utf8_encode_recursive($value);
        }
    } elseif (is_object($object)) {
        foreach ($object as $key => $value) {
            $object->$key = _utf8_encode_recursive($value);
        }
    }

    return $object;
}

function _json_encode($object)
{
    if (is_string($object)) {
        return $object;
    }
    if (empty($object)) {
        return json_encode($object);
    }

    // Ensure that all strings within the object are UTF-8 encoded
    $utf8_encoded_object = _utf8_encode_recursive($object);

    // Encode the object as JSON
    $json = json_encode($utf8_encoded_object);

    // If there's a JSON encoding error, log the error message and debug backtrace
    if (empty($json) && json_last_error()) {
        $errors[] = "_json_encode: Error Found: " . json_last_error_msg();
        foreach ($errors as $value) {
            _error_log($value);
        }
        _error_log(json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    }

    return $json;
}

function _json_decode($object, $associative = false)
{
    global $global;
    if (empty($object)) {
        return $object;
    }
    if (!is_string($object)) {
        return $object;
    }
    if (isValidURLOrPath($object)) {
        $content = file_get_contents($object);
        if (!empty($content)) {
            $object = $content;
        }
    }
    $json = json_decode($object, $associative);
    if ($json === null) {
        $object = str_replace(["\r", "\n"], ['\r', '\n'], $object);
        return json_decode($object, $associative);
    } else {
        return $json;
    }
}

function _log_session_performance($reason = 'unknown')
{
    global $global;
    static $performance_logged = false;

    // Evitar log duplicado
    if ($performance_logged || empty($global['session_start_time'])) {
        return;
    }

    if (isSessionStarted()) {
        $session_open_duration = microtime(true) - $global['session_start_time'];
        // Log if session was open for more than 2 seconds (blocking other requests)
        if ($session_open_duration > 2) {
            $script = $_SERVER['SCRIPT_NAME'] ?? 'unknown';
            _error_log("Session lock held for {$session_open_duration} seconds in {$script} ({$reason})", AVideoLog::$PERFORMANCE);
            _error_log(json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$PERFORMANCE);
        }
        $performance_logged = true;
        unset($global['session_start_time']);
    }
}

function _session_write_close()
{
    global $global;
    if (isSessionStarted()) {
        _log_session_performance('explicit_close');
        //_error_log(json_encode(debug_backtrace()));
        @session_write_close();
    }
}

function isSessionStarted()
{
    global $customSessionHandle;

    if (session_status() == PHP_SESSION_NONE) {
        return false;
    }
    if (session_status() == PHP_SESSION_ACTIVE) {
        return true;
    }
    // Check if a session variable exists in Memcached
    if (!empty($customSessionHandle) && $customSessionHandle->get(session_id()) !== false) {
        return true;
    } else {
        return false;
    }
}

function session_start_preload()
{
    global $_session_start_preload, $global;
    if (empty($global['systemRootPath'])) {
        return false;
    }
    if (!class_exists('AVideoConf')) {
        require $global['systemRootPath'] . 'objects/configuration.php';
    }
    if (!isset($_session_start_preload)) {
        $_session_start_preload = 1;
    } else {
        return false;
    }

    $config = new AVideoConf();

    // server should keep session data for AT LEAST 1 hour
    ini_set('session.gc_maxlifetime', $config->getSession_timeout());

    // The real PHP session cookie must also be cross-site compatible for embeds.
    _setSessionCookieParams($config->getSession_timeout());

    // Fix “set SameSite cookie to none” warning and check if cookie already set
    if (isset($_COOKIE['key'])) {
        // Cookie 'key' is already set, no need to set it again
        return true;
    }

    _setcookieInternal('key', 'value', time() + $config->getSession_timeout());
}


function _session_start(array $options = [])
{
    try {
        session_start_preload();

        // Start session first, then check the session ID
        // GET-based PHPSESSID is intentionally supported for cross-domain iframe
        // scenarios (view counting, CAPTCHA) where cookies cannot be shared.
        // Security properties:
        //  - Only accepted for non-logged-in users (see isLogged() check below).
        //  - Session is always regenerated after login (_session_regenerate_id in User::login),
        //    so a fixed pre-auth session ID cannot survive into an authenticated session.
        //  - Format is validated against PHP's allowed session-ID character set.
        if (isset($_GET['PHPSESSID']) && !_empty($_GET['PHPSESSID'])) {
            $PHPSESSID = $_GET['PHPSESSID'];
            unset($_GET['PHPSESSID']);
            // Reject any value that does not conform to PHP's session ID format
            // (alphanumeric + comma/hyphen, 22–256 chars) to prevent injection.
            if (!preg_match('/^[a-zA-Z0-9,\-]{22,256}$/', $PHPSESSID)) {
                $PHPSESSID = null;
            }

            // Start the session with the options
            $session = @session_start($options);
            // Track when session was opened to detect long session locks
            $global['session_start_time'] = microtime(true);

            if (empty($PHPSESSID)) {
                return $session;
            }

            // Now, check if session ID matches after session start
            if ($PHPSESSID === session_id()) {
                // Session ID already matches, do nothing
                return $session;
            }

            if (!User::isLogged()) {
                if ($PHPSESSID !== session_id()) {
                    _session_write_close();
                    session_id($PHPSESSID);
                    //_error_log("captcha: session_id changed to {$PHPSESSID}");
                }

                // Restart session after changing the session ID
                $session = @session_start($options);
                // Update session start time after restart
                $global['session_start_time'] = microtime(true);

                if (preg_match('/objects\/getCaptcha\.php/i', $_SERVER['SCRIPT_NAME'])) {
                    $regenerateSessionId = false;
                }

                if (!blackListRegenerateSession()) {
                    _error_log("captcha: session_id regenerated new session_id=" . session_id());
                    _session_regenerate_id();
                }

                return $session;
            } else {
                //_error_log("captcha: user logged we will not change the session ID PHPSESSID={$PHPSESSID} session_id=" . session_id());
            }
        } elseif (!isSessionStarted()) {
            //_error_log(json_encode(debug_backtrace()));
            $start = microtime(true);
            //_error_log('session_start 1');
            $session = @session_start($options);
            //_error_log('session_id '. session_id().' line='.__LINE__.' IP:'.getRealIpAddr().json_encode($options));
            //_error_log('session_start 2');
            $takes = microtime(true) - $start;
            if ($takes > 1) {
                _error_log('session_start takes ' . $takes . ' seconds to open', AVideoLog::$PERFORMANCE);
                _error_log(json_encode(debug_backtrace()), AVideoLog::$PERFORMANCE);
                //exit;
            }
            // Track when session was opened to detect long session locks
            $global['session_start_time'] = microtime(true);
            return $session;
        }
    } catch (Exception $exc) {
        _error_log("_session_start: " . $exc->getTraceAsString());
        return false;
    }
}


function _session_regenerate_id()
{
    $oldId = session_id();
    $session = $_SESSION;
    session_regenerate_id(true);
    _error_log('[SESSION_DEBUG] _session_regenerate_id: old=' . $oldId . ' new=' . session_id() . ' script=' . ($_SERVER['SCRIPT_NAME'] ?? '') . ' trace=' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6)));
    _resetcookie('PHPSESSID', session_id());
    _resetcookie(session_name(), session_id());
    $_SESSION = $session;
}

function uniqidV4()
{
    $randomString = openssl_random_pseudo_bytes(16);
    $time_low = bin2hex(substr($randomString, 0, 4));
    $time_mid = bin2hex(substr($randomString, 4, 2));
    $time_hi_and_version = bin2hex(substr($randomString, 6, 2));
    $clock_seq_hi_and_reserved = bin2hex(substr($randomString, 8, 2));
    $node = bin2hex(substr($randomString, 10, 6));

    /**
     * Set the four most significant bits (bits 12 through 15) of the
     * time_hi_and_version field to the 4-bit version number from
     * Section 4.1.3.
     * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
     */
    $time_hi_and_version = hexdec($time_hi_and_version);
    $time_hi_and_version = $time_hi_and_version >> 4;
    $time_hi_and_version = $time_hi_and_version | 0x4000;

    /**
     * Set the two most significant bits (bits 6 and 7) of the
     * clock_seq_hi_and_reserved to zero and one, respectively.
     */
    $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

    return sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
}


/**
 * @link https://github.com/php/php-src/issues/8218
 * @return bool
 */
function doesPHPVersioHasOBBug()
{
    return (version_compare(phpversion(), '8.1.4', '==') || version_compare(phpversion(), '8.0.17', '=='));
}


function getSystemAPIs()
{
    global $global;
    $obj = AVideoPlugin::getObjectData("API");
    $methodsList = array();

    $reflector = new ReflectionClass('API');
    $class_methods = get_class_methods('API');
    foreach ($class_methods as $key => $met) {
        if (preg_match("/(get|set)_api_(.*)/", $met, $matches)) {
            $methodsList[] = array($met, $reflector, $matches[1], $matches[2], 'API');
        }
    }

    $plugins = Plugin::getAllEnabled();
    foreach ($plugins as $value) {
        $p = AVideoPlugin::loadPlugin($value['dirName']);
        if (class_exists($value['dirName'])) {
            $class_methods = get_class_methods($value['dirName']);
            $reflector = new ReflectionClass($value['dirName']);
            foreach ($class_methods as $key => $met) {
                if (preg_match("/API_(get|set)_(.*)/", $met, $matches)) {
                    $methodsList[] = array($met, $reflector, $matches[1], $matches[2], $value['dirName']);
                }
            }
        }
    }

    $response = array();
    $plugins = array();
    foreach ($methodsList as $method) {
        if (!preg_match("/(get|set)_api_(.*)/", $method[0], $matches)) {
            if (!preg_match("/API_(get|set)_(.*)/", $method[0], $matches)) {
                continue;
            }
        }
        $reflector = $method[1];
        $comment = $reflector->getMethod($method[0])->getDocComment();
        $comment = str_replace(['{webSiteRootURL}', '{getOrSet}', '{APIPlugin}', '{APIName}', '{APISecret}'], [$global['webSiteRootURL'], $method[2], $method[4], $method[3], $obj->APISecret], $comment);
        $resp = array(
            'comment' => $comment,
            'method' => $method[0],
            'type' => $method[2],
            'action' => $method[3],
            'plugin' => $method[4],
        );
        $plugins[$method[4]][$method[0]] = $resp ;
        $response[] = $resp ;
    }
    return array('methodsList' => $methodsList, 'response' => $response, 'plugins' => $plugins);
}

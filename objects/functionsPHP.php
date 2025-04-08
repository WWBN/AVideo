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
    $domain = getDomain();
    if (version_compare(phpversion(), '7.3', '>=')) {
        $cookie_options = [
            'expires' => $expires,
            'path' => '/',
            'domain' => $domain,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ];
        setcookie($cookieName, $value, $cookie_options);
        $cookie_options['domain'] = 'www.' . $domain;
        setcookie($cookieName, $value, $cookie_options);
    } else {
        setcookie($cookieName, $value, (int) $expires, "/", $domain);
        setcookie($cookieName, $value, (int) $expires, "/", 'www.' . $domain);
    }
    $_COOKIE[$cookieName] = $value;
}

function _unsetcookie($cookieName)
{
    $domain = getDomain();
    $expires = strtotime("-10 years");
    $value = '';
    _setcookie($cookieName, $value, $expires);
    setcookie($cookieName, $value, (int) $expires, "/") && setcookie($cookieName, $value, (int) $expires);
    setcookie($cookieName, $value, (int) $expires, "/", str_replace("www", "", $domain));
    setcookie($cookieName, $value, (int) $expires, "/", "www." . $domain);
    setcookie($cookieName, $value, (int) $expires, "/", ".www." . $domain);
    setcookie($cookieName, $value, (int) $expires, "/", "." . $domain);
    setcookie($cookieName, $value, (int) $expires, "/", $domain);
    setcookie($cookieName, $value, (int) $expires, "/");
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

function _session_write_close()
{
    if (isSessionStarted()) {
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

    // each client should remember their session id for EXACTLY 1 hour
    session_set_cookie_params($config->getSession_timeout());

    // Fix “set SameSite cookie to none” warning and check if cookie already set
    if (isset($_COOKIE['key'])) {
        // Cookie 'key' is already set, no need to set it again
        return true;
    }

    if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
        setcookie('key', 'value', ['samesite' => 'None', 'secure' => true]);
    } else {
        header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');
        setcookie('key', 'value', time() + $config->getSession_timeout(), '/; SameSite=None; Secure');
    }
}


function _session_start(array $options = [])
{
    try {
        session_start_preload();

        // Start session first, then check the session ID
        if (isset($_GET['PHPSESSID']) && !_empty($_GET['PHPSESSID'])) {
            $PHPSESSID = $_GET['PHPSESSID'];
            unset($_GET['PHPSESSID']);

            // Start the session with the options
            $session = @session_start($options);

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
            return $session;
        }
    } catch (Exception $exc) {
        _error_log("_session_start: " . $exc->getTraceAsString());
        return false;
    }
}


function _session_regenerate_id()
{
    $session = $_SESSION;
    session_regenerate_id(true);
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


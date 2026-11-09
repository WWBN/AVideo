<?php

namespace Tests\Security\LiveRestreamHistoryFixtures;

class RestreamEndpointState
{
    public static $admin = false;
    public static $canStream = true;
    public static $destinations = [15 => 5, 16 => 1];
    public static $logs = [8 => [42, 15], 9 => [44, 15]];
    public static $minted = [];
    public static $fetched = [];
    public static $messages = [];
}

class AVideoLog
{
    public static $SECURITY = 'SECURITY';
}

class AVideoPlugin
{
    public static function isEnabledByName($name) { return true; }
    public static function getDataObjectIfEnabled($name) { return new \stdClass(); }
}

class User
{
    public static function getId() { return 5; }
    public static function isAdmin() { return RestreamEndpointState::$admin; }
    public static function canStream() { return RestreamEndpointState::$canStream; }
}

class Live
{
    public static function canRestream() { return User::canStream(); }
}

class Live_restreams
{
    private $id;
    public function __construct($id) { $this->id = $id; }
    public function getUsers_id() { return intval(RestreamEndpointState::$destinations[$this->id] ?? 0); }
}

class Live_restreams_logs
{
    private $id;
    public function __construct($id) { $this->id = $id; }
    public function getLive_transmitions_history_id() { return RestreamEndpointState::$logs[$this->id][0] ?? 0; }
    public function getLive_restreams_id() { return RestreamEndpointState::$logs[$this->id][1] ?? 0; }
    public static function getURLFromTransmitionAndRestream($history, $destination, $action)
    {
        return self::getURL($history, $destination, 0, $action);
    }
    public static function getURL($history, $destination, $log, $action)
    {
        RestreamEndpointState::$minted[] = [$history, $destination, $log, $action];
        return 'https://restream.invalid/?tokenForAction=fixture-token';
    }
}

class ForbiddenEndpoint extends \RuntimeException {}
class EndpointResponse extends \RuntimeException {}
function forbiddenPage($message) { throw new ForbiddenEndpoint($message); }
function __($message) { return $message; }
function header($value) {}
function debugLog($line) {}
function _error_log($message, $level = '') { RestreamEndpointState::$messages[] = $message; }
function url_get_contents($url, ...$options)
{
    RestreamEndpointState::$fetched[] = $url;
    return json_encode(['error' => false, 'msg' => '', 'content' => 'fixture log', 'isActive' => true]);
}

// Execute the endpoint's real authorization/control flow, with configuration, headers, exits and network isolated.
// No installation configuration is loaded and no files or accounts are written.
function runRestreamEndpoint($relativePath, $request)
{
    $previousRequest = $_REQUEST;
    $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/view/' . $relativePath);
    $source = preg_replace('/^\s*require_once[^\r\n]+;\s*$/m', '', $source);
    $debugStart = strpos($source, "\nfunction debugLog(");
    if ($debugStart !== false) {
        $source = substr($source, 0, $debugStart);
    }
    $body = '';
    foreach (token_get_all($source) as $token) {
        if (is_array($token)) {
            if ($token[0] === T_OPEN_TAG) {
                continue;
            }
            $body .= $token[0] === T_EXIT ? 'throw new EndpointResponse' : $token[1];
        } else {
            $body .= $token;
        }
    }
    $_REQUEST = $request;
    try {
        eval('namespace ' . __NAMESPACE__ . '; use stdClass; ' . $body);
        throw new \RuntimeException('Endpoint did not produce a response');
    } catch (ForbiddenEndpoint $exception) {
        return ['denied' => true];
    } catch (EndpointResponse $exception) {
        return json_decode($exception->getMessage(), true);
    } finally {
        $_REQUEST = $previousRequest;
    }
}

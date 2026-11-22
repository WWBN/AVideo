<?php

namespace Tests\Security\LiveEmbedFixtures;

class State
{
    public static $row;
    public static $rows = [];
    public static $groups = [];
    public static $userGroups = [];
    public static $admin = false;
    public static $logged = false;
    public static $hideUserGroups = false;
    public static $playlists = false;
    public static $ppv = false;
    public static $ppvDenied = [];
    public static $events = [];
}

class User
{
    public function __construct(...$arguments) {}
    public function getBdId() { return 5; }
    public static function isAdmin() { return State::$admin; }
    public static function isLogged() { return State::$logged; }
    public static function getId() { return State::$logged ? 5 : 0; }
}

class UserGroups
{
    public static function getUserGroups($id) { return State::$userGroups; }
}

class AVideoPlugin
{
    public static function getDataObject($name) { return (object) ['hideUserGroups' => State::$hideUserGroups]; }
    public static function getObjectDataIfEnabled($name) { return new \stdClass(); }
    public static function loadPlugin($name) { return new Live(); }
    public static function loadPluginIfEnabled($name)
    {
        return new class {
            public function verifyEmbedSecurity() { State::$events[] = 'embed-security'; }
        };
    }
    public static function getModeYouTubeLive($id) { State::$events[] = 'ppv'; }
    public static function isEnabledByName($name)
    {
        return $name === 'PlayLists' ? State::$playlists : State::$ppv;
    }
}

class LiveTransmitionHistory
{
    public static function getActiveLives(...$arguments) { return array_values(State::$rows); }
}

class Playlists_schedules
{
    public static function iskeyPlayListScheduled($key) { return false; }
}

class PayPerViewLive
{
    public static function canUserWatchNow($viewer, $owner) { return !in_array($owner, State::$ppvDenied, true); }
}

class Forbidden extends \RuntimeException {}
function forbiddenPage($message) { State::$events[] = 'forbidden'; throw new Forbidden($message); }
function _error_log($message) {}
function setLiveKey(...$arguments) { State::$events[] = 'set-key'; }
function isValidURL($url) { return filter_var($url, FILTER_VALIDATE_URL) !== false; }
function _json_decode($json) { return json_decode($json); }
function object_to_array($object) { return json_decode(json_encode($object), true); }
function matchWithRequest($row) { return true; }

// Use the production decisions with isolated identity/data. Never load installation configuration.
function productionMethod($file, $name)
{
    $tokens = token_get_all(file_get_contents(dirname(__DIR__, 2) . '/' . $file));
    foreach ($tokens as $index => $token) {
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }
        $nameIndex = $index + 1;
        while (is_array($tokens[$nameIndex]) && $tokens[$nameIndex][0] === T_WHITESPACE) {
            $nameIndex++;
        }
        if (!is_array($tokens[$nameIndex]) || $tokens[$nameIndex][1] !== $name) {
            continue;
        }
        $method = '';
        $depth = 0;
        for ($position = $index; $position < count($tokens); $position++) {
            $part = $tokens[$position];
            $method .= is_array($part) ? $part[1] : $part;
            if ($part === '{' || (is_array($part) && in_array($part[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++;
            } elseif ($part === '}' && --$depth === 0) {
                return preg_replace('/^\s*require_once[^\r\n]+;\s*$/m', '', $method);
            }
        }
    }
    throw new \RuntimeException('Missing production method: ' . $name);
}

eval('namespace ' . __NAMESPACE__ . ';
class LiveTransmition {
    protected $id;
    public function __construct($id) { $this->id = $id; State::$events[] = ["transmission", $id]; }
    public function getGroups() { return State::$groups[$this->id] ?? []; }
    public static function getFromRequest() { return State::$row; }
    public static function keyNameFix($key) { return $key; }
    ' . productionMethod('plugin/Live/Objects/LiveTransmition.php', 'userCanSeeTransmition') . '
}
class Live_schedule {
    protected $id;
    public function __construct($id) { $this->id = $id; State::$events[] = ["schedule", $id]; }
    public function getJson() { return json_encode(["usergoups" => State::$groups[$this->id] ?? []]); }
    public function getTitle() { return "Scheduled live"; }
    public function getKey() { return State::$row["key"]; }
    public function getLive_servers_id() { return 0; }
    ' . productionMethod('plugin/Live/Objects/Live_schedule.php', 'getUserGroups') . '
    ' . productionMethod('plugin/Live/Objects/Live_schedule.php', 'userCanSeeTransmition') . '
}
class Live {
    public static function getLiveServersIdRequest() { return 0; }
    public static function checkIfPasswordIsGood($key) { State::$events[] = "password"; }
    public static function getRegularPosterImage(...$arguments) { return "poster.jpg"; }
    public static function getLiveTransmitionObjectFromKey($key) {
        $row = State::$rows[$key] ?? null;
        if (!$row) { return false; }
        return empty($row["live_schedule"]) ? new LiveTransmition($row["id"]) : new Live_schedule($row["id"]);
    }
    public static ' . productionMethod('plugin/Live/Live.php', 'canSeeLiveFromLiveKey') . '
}');

function renderPreamble($relativePath, $query = [])
{
    $savedGet = $_GET;
    $savedRequest = $_REQUEST;
    $savedGlobals = [];
    foreach (['global', 'isLive'] as $name) {
        $savedGlobals[$name] = [array_key_exists($name, $GLOBALS), $GLOBALS[$name] ?? null];
    }
    try {
        $_GET = $_REQUEST = array_merge(['u' => 'channel'], $query);
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://site.invalid/', 'systemRootPath' => dirname(__DIR__, 2) . '/'];
        $source = file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);
        $source = substr($source, 5, strpos($source, '?>') - 5);
        $source = preg_replace('/^\s*require_once[^\r\n]+;\s*$/m', '', $source);
        $global = &$GLOBALS['global'];
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; ' . $source);
            return ['denied' => false, 'root' => $global['webSiteRootURL']];
        } catch (Forbidden $exception) {
            return ['denied' => true];
        } finally {
            $output = ob_get_clean();
            if ($output !== '') {
                throw new \RuntimeException('Unexpected output before renderer authorization');
            }
        }
    } finally {
        $_GET = $savedGet;
        $_REQUEST = $savedRequest;
        foreach ($savedGlobals as $name => [$existed, $value]) {
            if ($existed) {
                $GLOBALS[$name] = $value;
            } else {
                unset($GLOBALS[$name]);
            }
        }
    }
}

function selectLatestLive()
{
    $users_id = $categories_id = 0;
    $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/latestOrLive.php');
    $start = strpos($source, '$liveFound = false;');
    $end = strpos($source, '//var_dump($liveFound', $start);
    if ($start === false || $end === false) {
        throw new \RuntimeException('Live selection block not found');
    }
    eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));
    return $liveVideo ?? null;
}

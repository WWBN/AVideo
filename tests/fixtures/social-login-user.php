<?php

namespace Tests\Security\SocialLoginFixtures;

class AVideoLog
{
    public static $SECURITY = 'SECURITY';
    public static $ERROR = 'ERROR';
}

function _error_log($message, $type = '') {}
function _empty($value) { return empty($value); }
function getRealIpAddr() { return '127.0.0.1'; }
function encryptPassword($value) { return md5($value); }
function encryptPasswordVerify($value, $hash, $encoded = false) { return md5($value) === $hash; }
function isAVideoMobileApp() { return false; }
function isAVideoEncoder() { return false; }
function _session_start() {}
function _session_write_close() {}
function _session_regenerate_id() { UserStore::$regenerated++; }

class UserStore
{
    public static $rows = [];
    public static $writes = [];
    public static $failWrite = 0;
    public static $cookie;
    public static $signIns = [];
    public static $groups = [];
    public static $regenerated = 0;
    public static $verifications = [];
}

class UserAccountMutationLock
{
    public static function acquire($id) { return new self(); }
}

class ObjectYPT
{
    public static function clearSessionCache() {}
}

class AVideoPlugin
{
    public static function onUserSignIn($id) { UserStore::$signIns[] = $id; }
}

class UserGroups
{
    public static function updateUserGroups($id, $groups, $replace) { UserStore::$groups[$id] = $groups; }
}

class sqlDAL
{
    private static function storedRow($row)
    {
        if ($row !== false && is_array($row['externalOptions'] ?? null)) {
            $row['externalOptions'] = base64_encode(serialize($row['externalOptions']));
        }
        return $row;
    }

    public static function readSql($sql, $formats = '', $values = [], $refresh = false)
    {
        if (strpos($sql, 'LOCATE(') !== false) {
            $matches = [];
            foreach (UserStore::$rows as $id => $row) {
                $stored = self::storedRow($row);
                if (strpos(base64_decode($stored['externalOptions'] ?? ''), $values[0]) !== false) {
                    $matches[] = ['id' => $id];
                }
            }
            return (object) ['rows' => $matches];
        }
        if (preg_match('/WHERE\s+id = \?/', $sql)) {
            return self::storedRow(UserStore::$rows[$values[0]] ?? false);
        }
        $rows = UserStore::$rows;
        if (strpos($sql, 'ORDER BY id DESC') !== false) {
            krsort($rows);
        }
        foreach ($rows as $id => $row) {
            $matches = strcasecmp($row['user'], $values[0]) === 0;
            if (strpos($sql, 'OR email = ?') !== false) {
                $matches = $matches || strcasecmp($row['email'] ?? '', end($values)) === 0;
            }
            if ($matches && (strpos($sql, "status = 'a'") === false || $row['status'] === 'a')) {
                return self::storedRow($row + ['id' => $id]);
            }
        }
        return false;
    }

    public static function fetchAssoc($row) { return is_object($row) ? array_shift($row->rows) : $row; }
    public static function close($result) {}

    public static function writeSql($sql, $formats = '', $values = [])
    {
        global $global;
        UserStore::$writes[] = [$sql, $values];
        if (count(UserStore::$writes) === UserStore::$failWrite) {
            return false;
        }
        foreach (str_split($formats) as $index => $format) {
            if ($format === 'i') {
                $values[$index] = intval($values[$index]);
            }
        }
        if (strpos($sql, 'INSERT INTO users ') === 0) {
            if (strlen($values[0]) > 45) {
                return false;
            }
            $id = count(UserStore::$rows) + 1;
            $fields = ['user', 'password', 'email', 'name', 'isAdmin', 'canStream', 'canUpload', 'canCreateMeet',
                'status', 'photoURL', 'recoverPass', 'channelName', 'analyticsCode', 'externalOptions', 'phone', 'emailVerified'];
            UserStore::$rows[$id] = array_combine($fields, $values) + ['id' => $id];
            UserStore::$rows[$id]['externalOptions'] = unserialize(base64_decode($values[13] ?? '')) ?: [];
            $global['mysqli']->insert_id = $id;
        } elseif (strpos($sql, 'UPDATE users SET lastLogin') === 0) {
            UserStore::$rows[$values[0]]['lastLogin'] = 'fixture';
        } elseif (strpos($sql, 'UPDATE users SET user = ?') === 0) {
            preg_match_all('/(\w+)\s*=\s*\?/', $sql, $matches);
            $id = end($values);
            foreach ($matches[1] as $index => $field) {
                UserStore::$rows[$id][$field] = $field === 'externalOptions' ? unserialize(base64_decode($values[$index])) : $values[$index];
            }
        } else {
            throw new \RuntimeException('Unexpected fixture write: ' . $sql);
        }
        return true;
    }
}

// Extract whole methods with PHP tokens, so braces in strings/comments cannot truncate them.
// Only infrastructure is stubbed: account construction, persistence and the login decision run real code.
function loadSocialLoginUserFixture()
{
    $source = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
    $wanted = array_flip(['__construct', 'load', 'getUserDb', 'getEmail', 'getIsAdmin', 'getPhotoURL',
        'setPhotoURL', 'setEmail', 'setName', 'setIsAdmin', 'setEmailVerified', 'getEmailVerified', 'getIs_company', 'save',
        'userExists', 'createUserIfNotExists', 'getSocialLoginUserId', 'addExternalOptions',
        'setExternalOptions', 'getExternalOption', 'decodeExternalOption', 'login', 'find', 'setLastLogin']);
    $methods = '';
    $tokens = token_get_all($source);
    foreach ($tokens as $index => $token) {
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }
        $nameIndex = $index + 1;
        while (is_array($tokens[$nameIndex]) && $tokens[$nameIndex][0] === T_WHITESPACE) {
            $nameIndex++;
        }
        if (!is_array($tokens[$nameIndex]) || !isset($wanted[$tokens[$nameIndex][1]])) {
            continue;
        }
        $start = $index;
        while ($start > 0 && is_array($tokens[$start - 1]) && in_array($tokens[$start - 1][0], [T_WHITESPACE, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC], true)) {
            $start--;
        }
        $body = '';
        $depth = 0;
        $opened = false;
        for ($position = $start; $position < count($tokens); $position++) {
            $part = $tokens[$position];
            $body .= is_array($part) ? $part[1] : $part;
            if ($part === '{' || (is_array($part) && in_array($part[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++;
                $opened = true;
            } elseif ($part === '}' && --$depth === 0 && $opened) {
                break;
            }
        }
        // Framework bootstrap is deliberately omitted; no installation configuration is loaded.
        $methods .= str_replace('require_once $global[\'systemRootPath\'] . \'plugin/AVideoPlugin.php\';', '', $body) . "\n";
        unset($wanted[$tokens[$nameIndex][1]]);
    }
    if ($wanted) {
        throw new \RuntimeException('Missing production methods: ' . implode(', ', array_keys($wanted)));
    }
    eval('namespace ' . __NAMESPACE__ . ';
        #[\\AllowDynamicProperties]
        class User {
            private $id, $user, $password, $email, $name, $isAdmin, $canStream, $canUpload, $canCreateMeet,
                $canViewChart, $status, $photoURL, $backgroundURL, $recoverPass, $about, $channelName,
                $emailVerified, $analyticsCode, $externalOptions, $first_name, $last_name, $address,
                $zip_code, $country, $region, $city, $donationLink, $phone, $birth_date;
            private $is_company = 0;
            public const USER_LOGGED = 0, USER_NOT_VERIFIED = 1, USER_NOT_FOUND = 2, CAPTCHA_ERROR = 3, SYSTEM_ERROR = 5;
            public static function isAdmin() { return false; }
            public static function isLogged() { return !empty($_SESSION["user"]["id"]); }
            public static function getId() { return 0; }
            public static function _recommendChannelName($name, $try, $user, $id) { return $name ?: $user; }
            public static function getUserHash($id, $valid = "") { return "fixture-token-" . $id; }
            public static function checkLoginAttempts() { return true; }
            private static function setUserCookie($remember, $id, $user, $hash, $expires) { UserStore::$cookie = $id; }
            public static function sendVerificationLink($id) { UserStore::$verifications[] = $id; }
            ' . $methods . '
        }');
}

loadSocialLoginUserFixture();

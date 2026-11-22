<?php

namespace Tests\Security\LiveRestreamToken;

use PHPUnit\Framework\TestCase;

/**
 * GHSA-34m8-8x26-hhf7: getLiveKey.json.php returns a restream destination's stream key to whoever sends its
 * token. The token was encryptString($id), and any streamer gets encryptString(<own stream key>) back from the
 * API (livestream.key_v3), so setting the key to a destination id forged that destination's token.
 * The token is now an HMAC of the id (Live_restreams::getRestreamsToken()).
 */
function productionFunction($relativePath, $name, $isMethod)
{
    $tokens = token_get_all(file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath));
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
        $start = $index;
        if ($isMethod) {
            // keep the modifiers (public/private static) in front of "function"
            while ($start > 0 && (!is_array($tokens[$start - 1]) || in_array($tokens[$start - 1][0], [T_WHITESPACE, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC], true))) {
                if (!is_array($tokens[$start - 1])) {
                    break;
                }
                $start--;
            }
        }
        $code = '';
        $depth = 0;
        for ($position = $start; $position < count($tokens); $position++) {
            $part = $tokens[$position];
            $code .= is_array($part) ? $part[1] : $part;
            if ($part === '{' || (is_array($part) && in_array($part[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++;
            } elseif ($part === '}' && --$depth === 0) {
                return $code;
            }
        }
    }
    throw new \RuntimeException("Missing production function {$name} in {$relativePath}");
}

function _error_log($message, $level = 0) {}

class AVideoLog
{
    public static $SECURITY = 4;
}

class User
{
    public static function getId() { return 5; }
}

eval('namespace ' . __NAMESPACE__ . '; use stdClass; '
    . productionFunction('objects/functions.php', 'encrypt_decrypt', false)
    . productionFunction('objects/functions.php', 'encryptString', false)
    . productionFunction('objects/functions.php', 'decryptString', false)
    . 'class Live_restreams {'
    . productionFunction('plugin/Live/Objects/Live_restreams.php', 'getRestreamsToken', true)
    . productionFunction('plugin/Live/Objects/Live_restreams.php', 'getIdFromRestreamsToken', true)
    . productionFunction('plugin/Live/Objects/Live_restreams.php', 'getRestreamsTokenSignature', true)
    . productionFunction('plugin/Live/Objects/Live_restreams.php', 'signRestreamTokenData', true)
    . '}'
    . 'class Live_restreams_logs {'
    . productionFunction('plugin/Live/Objects/Live_restreams_logs.php', 'getToken', true)
    . productionFunction('plugin/Live/Objects/Live_restreams_logs.php', 'getTokenSignature', true)
    . productionFunction('plugin/Live/Objects/Live_restreams_logs.php', 'verifyToken', true)
    . '}'
    . 'class Live {' . productionFunction('plugin/Live/Live.php', 'getRestreamsIdFromToken', true) . '}');

class LiveRestreamTokenForgeryTest extends TestCase
{
    private $savedGlobal;

    protected function setUp(): void
    {
        $this->savedGlobal = [array_key_exists('global', $GLOBALS), $GLOBALS['global'] ?? null];
        $GLOBALS['global'] = [
            'salt' => 'fixture-salt-v1',
            'saltV2' => 'fixture-salt-v2-0123456789abcdef',
            'systemRootPath' => '/var/www/html/AVideo/',
        ];
    }

    protected function tearDown(): void
    {
        if ($this->savedGlobal[0]) {
            $GLOBALS['global'] = $this->savedGlobal[1];
        } else {
            unset($GLOBALS['global']);
        }
    }

    public function testGenuineTokenRoundTrips()
    {
        $token = Live_restreams::getRestreamsToken(17);
        $this->assertMatchesRegularExpression('/^17-[0-9a-f]{64}$/', $token, 'URL-safe: the restreamer does not urlencode it');
        $this->assertSame(17, Live::getRestreamsIdFromToken($token));
        $this->assertSame(17, Live::getRestreamsIdFromToken(" {$token}\n"));
    }

    public function testEncryptStringOracleNoLongerForgesATokenOrRestreamBypass()
    {
        // what the API hands back as key_v3 "?s=" when a streamer sets their own key to "1"
        $oracle = encryptString('1');
        $this->assertSame('1', decryptString($oracle), 'the fixture reproduces the reported oracle');
        $this->assertSame(0, Live::getRestreamsIdFromToken($oracle));
        $this->assertSame(0, Live::getRestreamsIdFromToken(encryptString('17')));
    }

    public function testTamperedOrMismatchedTokensAreRefused()
    {
        $token = Live_restreams::getRestreamsToken(17);
        [, $signature] = explode('-', $token);
        $this->assertSame(0, Live::getRestreamsIdFromToken('18-' . $signature), 'a signature is bound to its id');
        $this->assertSame(0, Live::getRestreamsIdFromToken('17-' . str_repeat('0', 64)));
        $this->assertSame(0, Live::getRestreamsIdFromToken('17-' . strtoupper($signature)));
        $this->assertSame(0, Live::getRestreamsIdFromToken('017-' . $signature));
        $this->assertSame(0, Live::getRestreamsIdFromToken('0-' . $signature));
        $this->assertSame(0, Live::getRestreamsIdFromToken('17'));
        $this->assertSame(0, Live::getRestreamsIdFromToken(['17', $signature]));
        $this->assertSame(0, Live::getRestreamsIdFromToken(''));
    }

    public function testTokenDependsOnTheServerSecretAndFailsClosedWithoutOne()
    {
        $token = Live_restreams::getRestreamsToken(17);
        $GLOBALS['global']['saltV2'] = 'another-installation-secret';
        $this->assertSame(0, Live::getRestreamsIdFromToken($token), 'another installation cannot reuse it');

        $GLOBALS['global']['saltV2'] = '';
        $GLOBALS['global']['salt'] = '';
        $this->assertSame('', Live_restreams::getRestreamsToken(17));
        $this->assertSame(0, Live::getRestreamsIdFromToken('17-' . hash_hmac('sha256', 'Live_restreams_getLiveKey|17', '')));
    }

    public function testGenuineActionTokenStillVerifies()
    {
        $token = Live_restreams_logs::getToken('start', 30, 17, 0);
        $obj = Live_restreams_logs::verifyToken($token);
        $this->assertIsObject($obj);
        $this->assertSame('start', $obj->action);
        $this->assertSame(17, $obj->live_restreams_id);
        $this->assertSame(30, $obj->live_transmitions_history_id);
        $this->assertSame(5, $obj->users_id);
        // string ids (request values) round-trip too
        $this->assertIsObject(Live_restreams_logs::verifyToken(Live_restreams_logs::getToken('log', '30', '17', '9', '5')));
    }

    public function testActionTokenFromTheEncryptStringOracleIsRefused()
    {
        // verifyTokenForAction.json.php answers a valid action token with a getLiveKey restreamsToken for its
        // live_restreams_id, so a forged action token would reopen the same leak. The live key column holds 255
        // characters, enough for this JSON, so a streamer can get encryptString() of it from the API.
        $forged = ['action' => 'log', 'live_restreams_logs_id' => 0, 'live_transmitions_history_id' => 30, 'live_restreams_id' => 17, 'time' => time(), 'users_id' => 5];
        $this->assertLessThanOrEqual(255, strlen(json_encode($forged)));
        $this->assertFalse(Live_restreams_logs::verifyToken(encryptString(json_encode($forged))));

        $forged['signature'] = str_repeat('0', 64);
        $this->assertFalse(Live_restreams_logs::verifyToken(encryptString(json_encode($forged))));

        // a genuine signature does not carry over to other ids, actions or users
        $genuine = json_decode(decryptString(Live_restreams_logs::getToken('log', 30, 17, 0)), true);
        foreach (['live_restreams_id' => 18, 'action' => 'stop', 'users_id' => 6, 'live_transmitions_history_id' => 31] as $field => $value) {
            $changed = $genuine;
            $changed[$field] = $value;
            $this->assertFalse(Live_restreams_logs::verifyToken(encryptString(json_encode($changed))), $field);
        }
        $this->assertFalse(Live_restreams_logs::verifyToken(encryptString('[1]')));
    }

    public function testActionTokenExpiryAndSecretStillApply()
    {
        $old = json_decode(decryptString(Live_restreams_logs::getToken('log', 30, 17, 0)), true);
        $old['time'] = time() - 7200;
        $old['signature'] = Live_restreams::signRestreamTokenData('Live_restreams_logs_action', json_encode(['log', '0', '30', '17', (string) $old['time'], '5']));
        $this->assertFalse(Live_restreams_logs::verifyToken(encryptString(json_encode($old))), 'still expires after 1 hour');

        $token = Live_restreams_logs::getToken('log', 30, 17, 0);
        $GLOBALS['global']['saltV2'] = $GLOBALS['global']['salt'] = '';
        $this->assertFalse(Live_restreams_logs::verifyToken($token), 'no secret, no token');
    }

    public function testEveryMintingSiteUsesTheSignedToken()
    {
        $root = dirname(__DIR__, 2) . '/';
        foreach (['plugin/Live/Live.php', 'plugin/Live/Objects/Live_restreams.php', 'plugin/Live/view/Live_restreams/verifyTokenForAction.json.php'] as $file) {
            $source = file_get_contents($root . $file);
            $this->assertDoesNotMatchRegularExpression("/'restreamsToken'\]?\s*(=>|=)\s*encryptString\(/", $source, $file);
            $this->assertDoesNotMatchRegularExpression('/=>\s*encryptString\(\$obj->live_restreams_id\)/', $source, $file);
        }
        $this->assertStringNotContainsString('decryptString', productionFunction('plugin/Live/Live.php', 'getRestreamsIdFromToken', true));
    }
}

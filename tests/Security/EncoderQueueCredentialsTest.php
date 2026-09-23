<?php

namespace Tests\Security\EncoderQueueCredentials;

use PHPUnit\Framework\TestCase;

$source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
$start = strpos($source, 'function sendToEncoder(');
$end = strpos($source, 'function getExtension(', $start);
eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));

function _error_log($message) {}
class Video {
    public static $missing = false;
    public static $posted;
    public static function getVideoLight($id) {
        return self::$missing ? false : ['id' => $id, 'users_id' => 37, 'filename' => 'fixture'];
    }
    public static function postToEncoderQueue($fields) {
        self::$posted = $fields;
        return (object) ['error' => false];
    }
}
class User {
    public static $canUpload = true;
    public static $signedFor;
    public function __construct($id) {}
    public function getUser() { return 'video-owner'; }
    public function getCanUpload() { return self::$canUpload; }
    public function getBdId() { return 37; }
    public function getPassword() { throw new \RuntimeException('Do not send a stored password hash'); }
    public static function getUserHash($id, $valid = '+7 days') {
        self::$signedFor = [$id, $valid];
        return '_user_hash_test-fixture';
    }
}

class EncoderQueueCredentialsTest extends TestCase
{
    private $previousGlobal;
    protected function setUp(): void
    {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://streamer.example.test/'];
        Video::$missing = false;
        Video::$posted = null;
        User::$canUpload = true;
        User::$signedFor = null;
    }
    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->previousGlobal;
    }
    public function testHeadlessQueueUsesExpiringCredentialForVideoOwner()
    {
        $result = sendToEncoder(71, 'https://streamer.example.test/original', true, 'inputHLS');
        $this->assertFalse($result->error);
        $this->assertSame([37, '+7 days'], User::$signedFor);
        $this->assertSame('_user_hash_test-fixture', Video::$posted['pass']);
        $this->assertSame('video-owner', Video::$posted['user']);
        $this->assertSame(71, Video::$posted['videos_id']);
        $this->assertSame(1, Video::$posted['inputHLS']);
    }
    public function testOwnerPermissionCheckStillPreventsQueueing()
    {
        User::$canUpload = false;
        $this->assertFalse(sendToEncoder(71, 'https://streamer.example.test/original', true, 'inputHLS'));
        $this->assertNull(User::$signedFor);
        $this->assertNull(Video::$posted);
    }
    public function testMissingVideoDoesNotIssueCredential()
    {
        Video::$missing = true;
        $this->assertFalse(sendToEncoder(71, 'https://streamer.example.test/original', true, 'inputHLS'));
        $this->assertNull(User::$signedFor);
        $this->assertNull(Video::$posted);
    }
}

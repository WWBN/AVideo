<?php
namespace Tests\Security\EncoderJobStatus;

use PHPUnit\Framework\TestCase;

class User {
    public static $logged = false, $admin = false, $upload = true;
    public static function isLogged() { return self::$logged; }
    public static function isAdmin() { return self::$admin; }
    public static function canUpload() { return self::$upload; }
}
class Video {
    public static $exists = true, $valid = true, $editable = true;
    private $id;
    public function __construct($title, $filename, $id, $refresh) { $this->id = self::$exists ? $id : 0; }
    public function getId() { return $this->id; }
    public static function getVideoIdFromHash($hash) { return self::$valid ? 45 : 0; }
    public static function canEncoderEdit($id) { return self::$editable; }
    public function getVideoIdHash() { return 'renewed-job-authorization'; }
}
function decryptString($hash) {
    return $hash === 'signed-job' ? json_encode(['videos_id' => 45, 'timeout' => 1, 'ownerFingerprint' => 'owner-binding']) : '';
}
function useVideoHashOrLogin() { User::$logged = true; }
function getToken($ttl, $scope) { return 'renewed-upload-authorization'; }

class EncoderJobStatusTest extends TestCase
{
    private $requestBefore;
    protected function setUp(): void {
        $this->requestBefore = $_REQUEST;
        if (!class_exists(__NAMESPACE__ . '\\EncoderJobStatus')) {
            eval('namespace ' . __NAMESPACE__ . ';' . substr(file_get_contents(APP_ROOT . '/objects/EncoderJobStatus.php'), 5));
        }
        User::$logged = User::$admin = false;
        User::$upload = Video::$exists = Video::$valid = Video::$editable = true;
    }
    protected function tearDown(): void { $_REQUEST = $this->requestBefore; }
    public function testAnonymousOrDifferentJobCannotProbeTheDestination() {
        Video::$exists = false;
        $this->assertSame('authentication_required', EncoderJobStatus::inspect(45, 'invalid')['code']);
    }
    public function testJobIdMustMatchItsSignedAssociation() {
        $result = EncoderJobStatus::inspect(46, 'signed-job');
        $this->assertSame('authentication_required', $result['code']);
        $this->assertArrayNotHasKey('video_id_hash', $result);
    }
    public function testDeletedDestinationIsExplicitEvenForAnExpiredAssociation() {
        Video::$exists = Video::$valid = false;
        $result = EncoderJobStatus::inspect(45, 'signed-job');
        $this->assertSame('destination_deleted', $result['code']);
        $this->assertFalse($result['retryable']);
        $this->assertFalse(User::$logged);
        $this->assertArrayNotHasKey('encoderChunkToken', $result);
    }
    public function testExpiredOrRevokedAuthorizationCannotBeRenewed() {
        Video::$valid = false;
        $result = EncoderJobStatus::inspect(45, 'signed-job');
        $this->assertSame('authentication_required', $result['code']);
        $this->assertFalse(User::$logged);
        $this->assertArrayNotHasKey('video_id_hash', $result);
    }
    public function testValidJobAuthorizationRenewsOnlyItsOwnDestination() {
        $result = EncoderJobStatus::inspect(45, 'signed-job');
        $this->assertFalse($result['error']);
        $this->assertSame(45, $result['destination_id']);
        $this->assertSame('renewed-job-authorization', $result['video_id_hash']);
        $this->assertArrayNotHasKey('pass', $result);
    }
    public function testUploadAndOwnershipRestrictionsRemainEnforced() {
        User::$logged = true;
        User::$upload = false;
        $this->assertSame('uploads_disabled', EncoderJobStatus::inspect(45, 'signed-job')['code']);
        User::$upload = true;
        Video::$editable = false;
        $this->assertSame('destination_access_denied', EncoderJobStatus::inspect(45, 'signed-job')['code']);
    }
    public function testAuthenticatedAdminCanIdentifyDeletedLegacyDestinations() {
        User::$logged = User::$admin = true;
        Video::$exists = false;
        $this->assertSame('destination_deleted', EncoderJobStatus::inspect(45, '')['code']);
    }
}

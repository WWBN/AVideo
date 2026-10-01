<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * objects/aVideoEncoderAlert.json.php makes the site send e-mail, so it must authenticate and
 * authorize before doing anything, and must never let the caller choose the recipient.
 */
class EncoderAlertEndpointTest extends TestCase
{
    private $source;

    protected function setUp(): void
    {
        $this->source = file_get_contents(dirname(__DIR__, 2) . '/objects/aVideoEncoderAlert.json.php');
    }

    private function position($needle)
    {
        $position = strpos($this->source, $needle);
        $this->assertNotFalse($position, 'Missing: ' . $needle);
        return $position;
    }

    public function testAuthenticationAndAuthorizationRunBeforeAnyWork()
    {
        $work = $this->position('try {');
        $this->assertLessThan($work, $this->position('useVideoHashOrLogin();'));
        $this->assertLessThan($work, $this->position("if (!User::isLogged()) {\n    forbiddenPage("));
        $this->assertLessThan($work, $this->position('!User::canUpload() || !Video::canEncoderEdit($videos_id)'));
        $this->assertLessThan($work, $this->position("enforceRateLimit('aVideoEncoderAlert_' . \$videos_id"));
        $this->assertLessThan($work, $this->position("enforceRateLimit('aVideoEncoderAlert_system_' . User::getId()"));
    }

    public function testRecipientComesFromTheDatabase()
    {
        $this->position('$users_id = Video::getOwner($videos_id);');
        $this->position('$users_id = User::getId();');
        $this->position('User::getEmailDb($users_id)');
        foreach (["\$_REQUEST['email']", "\$_REQUEST['to']", "\$_POST['email']", "\$_REQUEST['users_id']"] as $requestRecipient) {
            $this->assertStringNotContainsString($requestRecipient, $this->source);
        }
    }

    public function testOwnerIsNotMailedOnceTheVideoNoLongerWaitsForTheEncoder()
    {
        // An old failed job stays in the Encoder queue after the video is sent again.
        $gate = $this->position('!in_array($video->getStatus(), $stillWaitingForEncoder, true)');
        $this->assertLessThan($this->position('sendSiteEmail('), $gate);
    }

    public function testResponseNeverCarriesVideoIdentifiers()
    {
        // Encoder::sendToStreamer() saves videos_id/video_id_hash from a response into the job.
        $this->assertDoesNotMatchRegularExpression('/\$obj->(videos_id|video_id|video_id_hash)\s*=/', $this->source);
    }

    public function testUnexpectedErrorsAreNotLeaked()
    {
        $this->position("\$obj->msg = 'An error occurred';");
        $this->assertStringNotContainsString('$obj->msg = $th->getMessage()', $this->source);
    }
}

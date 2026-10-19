<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * SocialMediaPublisher keeps each user's YouTube/Facebook/LinkedIn OAuth access and refresh tokens
 * (publisher_user_preferences.json). They must never reach the browser or the log, and the
 * connection must only be accepted from the restream.ypt.me broker.
 *
 * These checks read the source: the endpoints need a database and a session. The browser part is
 * exercised by tests/social-media-publisher-message-origin.test.cjs and the upload logic by
 * tests/Unit/YouTubePublisherTest.php.
 */
class SocialMediaPublisherTokenExposureTest extends TestCase
{
    private function source($path)
    {
        return file_get_contents(\APP_ROOT . '/plugin/SocialMediaPublisher/' . $path);
    }

    public function testConnectionListDoesNotReturnTheTokens()
    {
        $source = $this->source('View/Publisher_user_preferences/list.json.php');
        $this->assertStringContainsString('SocialMediaPublisher::getPublicConnectionRow($rows[$key])', $source);
        $this->assertMatchesRegularExpression("/unset\(\\\$row\['json'\], \\\$row\['jsonObject'\]\)/", $this->source('SocialMediaPublisher.php'));
    }

    public function testIsConnectedAndRefreshReturnOnlyTheOutcome()
    {
        $isConnected = $this->source('isConnected.json.php');
        $this->assertStringContainsString('$obj->row = SocialMediaPublisher::getPublicConnectionRow($row);', $isConnected);
        $this->assertStringNotContainsString('$obj->revalidate', $isConnected);

        $refresh = $this->source('refresh.json.php');
        $this->assertStringNotContainsString('$obj = SocialMediaPublisher::revalidateTokenAndSave', $refresh);
        $this->assertStringNotContainsString('json_encode($resp', $refresh);
        // owners can renew their own connection, other users cannot
        $this->assertStringContainsString('!User::isAdmin() && $o->getUsers_id() != User::getId()', $refresh);
    }

    public function testTheBrokerRefreshURLIsNeitherReturnedNorLogged()
    {
        $source = $this->source('SocialMediaPublisher.php');
        $this->assertStringNotContainsString('$response->url', $source);
        // $failOnError = true makes url_get_contents() skip logging the URL (it carries the refresh token)
        $this->assertStringContainsString("url_get_contents(\$url, '', 30, false, false, true, false, true)", $source);
        $this->assertStringNotContainsString('json_encode($resp)', $source);
        $this->assertStringNotContainsString("json is empty {\$pub['json']}", $source);
    }

    public function testUploaderDoesNotDumpOrLogTokens()
    {
        $source = $this->source('Objects/SocialUploader.php');
        $this->assertStringNotContainsString('var_dump($line, $msg, $obj)', $source);
        $this->assertStringNotContainsString('"SocialMediaPublisher::upload json=" . json_encode($json)', $source);
    }

    public function testVideoLogListDoesNotReturnTheYouTubeResumeSession()
    {
        $source = $this->source('View/Publisher_video_publisher_logs/list.json.php');
        $this->assertStringContainsString("unset(\$rows[\$key]['json'], \$rows[\$key]['details']);", $source);
        $this->assertStringContainsString('YouTubePublisher::redactDetails(', $source);
    }

    public function testPublishPageRequiresEditPermissionAndEscapesTheTitle()
    {
        $source = $this->source('uploadVideo.php');
        $this->assertStringContainsString('if (!Video::canEdit($videos_id))', $source);
        $this->assertStringNotContainsString("value=\"<?php echo \$value['title']; ?>\"", $source);
        $this->assertStringContainsString("value=\"<?php echo \$toText(\$value['title']); ?>\"", $source);
    }

    public function testUploadWorkerOnlyRunsFromTheCommandLine()
    {
        $source = $this->source('youtubeUploadWorker.php');
        $check = strpos($source, 'if (!isCommandLineInterface())');
        $this->assertNotFalse($check);
        $this->assertLessThan(strpos($source, 'YouTubePublisher::runWorker'), $check);
    }

    public function testJobActionsCheckTheOwner()
    {
        $publisher = $this->source('Objects/YouTubePublisher.php');
        $this->assertStringContainsString("\$log->getUsers_id() !== intval(\$users_id) && !\$isAutomatic", $publisher);
        $this->assertStringContainsString("\$log->getUsers_id() !== intval(\$users_id)) {", $publisher);
        $job = $this->source('youtubeJob.json.php');
        $this->assertStringContainsString('YouTubePublisher::resume($logs_id, User::getId())', $job);
        $this->assertStringContainsString('YouTubePublisher::cancel($logs_id, User::getId())', $job);
        $this->assertStringContainsString('forbidIfNotPost();', $job);
    }

    public function testYouTubeGalleryDoesNotLogTheApiKey()
    {
        $source = file_get_contents(\APP_ROOT . '/plugin/YouTubeAPI/YouTubeAPI.php');
        $this->assertStringNotContainsString('developer_key={$DEVELOPER_KEY}', $source);
    }
}

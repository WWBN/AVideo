<?php

namespace Tests\Unit\EncoderAlert;

use PHPUnit\Framework\TestCase;

// Exercise the real class with isolated helpers; loading the full Streamer would bootstrap the
// production database and plugins.
$source = file_get_contents(dirname(__DIR__, 2) . '/objects/EncoderAlert.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($source, strpos($source, 'class EncoderAlert')));

function __($message, $allowHTML = false)
{
    // Same escaping as locale/function.php
    return $allowHTML ? $message : str_replace(["'", '"', '<', '>'], ['&apos;', '&quot;', '&lt;', '&gt;'], $message);
}
function secondsToHumanTiming($seconds, $precision = 0)
{
    return intval($seconds / 60) . ' minutes';
}
function humanFileSize($bytes)
{
    return round($bytes / 1073741824, 1) . ' GB';
}

class EncoderAlertTest extends TestCase
{
    private function owner(array $fields = [])
    {
        return EncoderAlert::readOwnerAlert(array_merge([
            'type' => 'processing', 'videos_id' => 5, 'queue_id' => 77, 'queue_status' => 'encoding',
            'minutes' => 65, 'queue_position' => 0, 'reason' => '', 'retention_days' => 7,
        ], $fields));
    }

    public function testUnknownOwnerTypeIsRejected()
    {
        $this->assertFalse(EncoderAlert::readOwnerAlert(['type' => 'system']));
        $this->assertFalse(EncoderAlert::readOwnerAlert(['type' => '<b>error</b>']));
        $this->assertFalse(EncoderAlert::readOwnerAlert([]));
    }

    public function testOwnerAlertValuesAreWhitelistedAndCast()
    {
        $alert = $this->owner(['queue_status' => '<script>', 'reason' => 'rm -rf /', 'minutes' => '-5', 'queue_id' => '12abc']);
        $this->assertSame('', $alert['queue_status']);
        $this->assertSame('unknown', $alert['reason']);
        $this->assertSame(0, $alert['minutes']);
        $this->assertSame(12, $alert['queue_id']);
    }

    public function testTitleAndNameAreEscapedInTheBodyAndPlainInTheSubject()
    {
        $mail = EncoderAlert::buildOwnerEmail($this->owner(), "<script>x</script>Trip \"2026\"\r\nBcc: a@b.c", '<i>Ann</i>', 'https://site.test/mvideos?video_id=5');
        $this->assertStringNotContainsString('<script>', $mail['body']);
        $this->assertStringNotContainsString('<i>', $mail['body']);
        $this->assertStringContainsString('Trip &quot;2026&quot;', $mail['body']);
        $this->assertStringContainsString('Trip "2026" Bcc: a@b.c', $mail['subject']);
        $this->assertStringNotContainsString("\n", $mail['subject']);
        $this->assertStringContainsString('Hello Ann,', $mail['body']);
    }

    public function testProcessingAlertExplainsTheStageWithoutAskingForAReupload()
    {
        $mail = EncoderAlert::buildOwnerEmail($this->owner(), 'Clip', 'Ann', '');
        $this->assertStringContainsString('taking longer than usual', $mail['subject']);
        $this->assertMatchesRegularExpression('/Current stage<\/td><td[^>]*>Encoding<\/td>/', $mail['body']);
        $this->assertMatchesRegularExpression('/Processing for<\/td><td[^>]*>65 minutes<\/td>/', $mail['body']);
        $this->assertStringContainsString('Your video is taking longer than usual', $mail['body']);
        $this->assertStringContainsString('#d97706', $mail['body']);
        $this->assertStringContainsString('You do not need to upload the video again.', $mail['body']);
        $this->assertStringContainsString('Reminders stop after 7 days.', $mail['body']);
        $this->assertStringNotContainsString('<a href', $mail['body']);
    }

    public function testWaitingAlertShowsTheQueuePosition()
    {
        $mail = EncoderAlert::buildOwnerEmail($this->owner(['type' => 'waiting', 'queue_status' => 'queue', 'queue_position' => 3]), 'Clip', '', '');
        $this->assertMatchesRegularExpression('/Position in the queue<\/td><td[^>]*>3<\/td>/', $mail['body']);
        $this->assertStringContainsString('Hello,', $mail['body']);
    }

    public function testErrorAlertsGiveTheReasonAndAJobReference()
    {
        $mail = EncoderAlert::buildOwnerEmail($this->owner(['type' => 'error', 'queue_status' => 'error', 'reason' => 'conversion_failed']), 'Clip', 'Ann', '');
        $this->assertStringContainsString('could not be processed', $mail['subject']);
        $this->assertStringContainsString('could not be converted', $mail['body']);
        $this->assertStringContainsString('Encoder job #77', $mail['body']);
        $this->assertStringContainsString('#dc2626', $mail['body']);
        $this->assertStringContainsString('upload the video again', $mail['body']);

        $mail = EncoderAlert::buildOwnerEmail($this->owner(['type' => 'error_reminder', 'minutes' => 1500, 'reason' => 'worker_stopped']), 'Clip', 'Ann', '');
        $this->assertStringContainsString('still could not be processed', $mail['subject']);
        $this->assertStringContainsString('in error for 1500 minutes', $mail['body']);
        $this->assertStringContainsString('stopped unexpectedly', $mail['body']);
    }

    public function testTransferFailureDoesNotAskForANewUpload()
    {
        $mail = EncoderAlert::buildOwnerEmail($this->owner(['type' => 'error', 'reason' => 'transfer_failed']), 'Clip', 'Ann', '');
        $this->assertStringContainsString('could not be sent back', $mail['body']);
        $this->assertStringContainsString('you do not need to upload the video again', $mail['body']);
        $this->assertStringNotContainsString('Please upload the video again', $mail['body']);
    }

    public function testManageLinkIsEscaped()
    {
        $mail = EncoderAlert::buildOwnerEmail($this->owner(), 'Clip', 'Ann', 'https://site.test/mvideos?video_id=5&x="><script>');
        $this->assertStringContainsString('href="https://site.test/mvideos?video_id=5&amp;x=&quot;&gt;&lt;script&gt;"', $mail['body']);
    }

    public function testReminderFooterFollowsTheEncoderInterval()
    {
        // Older Encoders send no interval: they repeat once per day.
        $this->assertSame(1440, $this->owner()['reminder_minutes']);

        $mail = EncoderAlert::buildOwnerEmail($this->owner(['reminder_minutes' => 360]), 'Clip', 'Ann', '');
        $this->assertStringContainsString('one reminder every 360 minutes', $mail['body']);
        $this->assertStringNotContainsString('per day', $mail['body']);

        $mail = EncoderAlert::buildOwnerEmail($this->owner(['reminder_minutes' => 0]), 'Clip', 'Ann', '');
        $this->assertStringNotContainsString('reminder', $mail['body']);
        $this->assertStringNotContainsString('Reminders stop', $mail['body']);
    }

    public function testSystemAlertValidation()
    {
        $this->assertFalse(EncoderAlert::readSystemAlert(['check' => 'reboot']));
        $alert = EncoderAlert::readSystemAlert([
            'check' => 'error_spike', 'errors_last_hour' => '6', 'encoder_url' => 'javascript:alert(1)',
            'last_error' => '<b>' . str_repeat('x', 300) . '</b>',
        ]);
        $this->assertSame('', $alert['encoder_url']);
        $this->assertSame(6, $alert['errors_last_hour']);
        $this->assertSame(200, strlen($alert['last_error']));
        $this->assertStringNotContainsString('<b>', $alert['last_error']);
    }

    public function testSystemEmailsEscapeEncoderValues()
    {
        $alert = EncoderAlert::readSystemAlert(['check' => 'error_spike', 'errors_last_hour' => 5, 'last_error' => 'bad "quote" & <tag', 'encoder_url' => 'https://encoder.test/']);
        $mail = EncoderAlert::buildSystemEmail($alert);
        $this->assertStringContainsString('5 videos failed in the last hour.', $mail['body']);
        $this->assertStringContainsString('bad &quot;quote&quot; &amp; ', $mail['body']);
        $this->assertStringContainsString('href="https://encoder.test/"', $mail['body']);

        $disk = EncoderAlert::buildSystemEmail(EncoderAlert::readSystemAlert(['check' => 'disk_low', 'disk_free' => 2 * 1073741824, 'disk_total' => 100 * 1073741824]));
        $this->assertStringContainsString('low disk space', $disk['subject']);
        $this->assertStringContainsString('Only 2 GB of 100 GB is free (2%).', $disk['body']);

        $stalled = EncoderAlert::buildSystemEmail(EncoderAlert::readSystemAlert(['check' => 'queue_stalled', 'waiting' => 4, 'oldest_waiting_minutes' => 45]));
        $this->assertStringContainsString('4 videos are waiting, the oldest for 45 minutes', $stalled['body']);
        $this->assertStringContainsString('every few hours', $stalled['body']);
        $this->assertStringNotContainsString('<a href', $stalled['body']);

        $repeat = EncoderAlert::buildSystemEmail(EncoderAlert::readSystemAlert(['check' => 'queue_stalled', 'reminder_minutes' => 360]));
        $this->assertStringContainsString('repeated at most every 360 minutes', $repeat['body']);
    }
}

<?php

namespace Tests\Unit\AsyncEmail;

use PHPUnit\Framework\TestCase;

$asyncMailSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsMail.php');
$asyncMailStart = strpos($asyncMailSource, 'function sendSiteEmailAsync(');
$asyncMailEnd = strpos($asyncMailSource, 'function sendBulkEmail(', $asyncMailStart);
eval('namespace ' . __NAMESPACE__ . ';' . substr($asyncMailSource, $asyncMailStart, $asyncMailEnd - $asyncMailStart));

class MailProbe
{
    public static $result;
    public static $logs = [];
}
function _json_encode($value) { return json_encode($value); }
function _error_log($message) { MailProbe::$logs[] = $message; }
function execAsync($command) { return MailProbe::$result; }

class AsyncEmailTest extends TestCase
{
    public static function launchResults()
    {
        return ['empty output' => [[]], 'output' => [['queued']], 'pid' => [123], 'unknown pid' => [null]];
    }

    /** @dataProvider launchResults */
    public function testLoggingPreservesAsyncResultAndQueuedEmail($result)
    {
        global $global;
        $savedGlobal = $global;
        $directory = sys_get_temp_dir() . '/avideo-mail-' . bin2hex(random_bytes(8));
        mkdir($directory . '/videos', 0700, true);
        try {
            $global = ['systemRootPath' => $directory . '/'];
            MailProbe::$result = $result;
            MailProbe::$logs = [];
            $this->assertSame($result, sendSiteEmailAsync(['one@example.test', 'one@example.test'], 'Test subject', 'Test body'));
            $this->assertSame('sendSiteEmailAsync end ' . json_encode($result), end(MailProbe::$logs));
            $files = glob($directory . '/videos/emails_*.log');
            $this->assertCount(1, $files);
            $queued = json_decode(file_get_contents($files[0]), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame(['one@example.test'], $queued['to']);
            $this->assertSame('Test subject', $queued['subject']);
            $this->assertSame('Test body', $queued['message']);
        } finally {
            foreach (glob($directory . '/videos/emails_*.log') as $file) { unlink($file); }
            rmdir($directory . '/videos');
            rmdir($directory);
            $global = $savedGlobal;
        }
    }
}

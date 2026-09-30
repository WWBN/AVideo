<?php

use PHPUnit\Framework\TestCase;

class ReportVideoRegressionTest extends TestCase
{
    /** @dataProvider requests */
    public function testReportAndBlockFlows($scenario, $error, $message = null, $forbidden = false): void
    {
        $output = [];
        $exitCode = 0;
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__DIR__) . '/report-video-regression.php')
            . ' ' . escapeshellarg(base64_encode(json_encode($scenario))) . ' 2>&1';
        exec($command, $output, $exitCode);
        $json = implode("\n", $output);
        $this->assertSame(0, $exitCode, $json);
        $response = json_decode($json, true);
        $this->assertIsArray($response, $json);
        $this->assertSame($error, $response['error']);
        if ($message !== null) $this->assertSame($message, $response['msg']);
        $this->assertSame($forbidden, !empty($response['forbiddenPage']));
    }

    public function requests(): array
    {
        return [
            'plugin cache, reasons and comment filtering' => [['endpoint' => 'methods'], false],
            'JSON block with active session' => [['endpoint' => 'block', 'body' => ['users_id' => 9]], false],
            'JSON block with credentials' => [['endpoint' => 'block', 'logged' => false, 'body' => ['users_id' => 9, 'user' => 'fixture', 'pass' => 'fixture']], false],
            'legacy form block' => [['endpoint' => 'block', 'form' => ['users_id' => 9]], false],
            'anonymous block' => [['endpoint' => 'block', 'logged' => false, 'body' => ['users_id' => 9]], true, 'User not logged', true],
            'self block' => [['endpoint' => 'block', 'body' => ['users_id' => 7]], true, 'You cannot block yourself'],
            'missing user block' => [['endpoint' => 'block', 'body' => ['users_id' => 404]], true, 'User not found'],
            'GET block' => [['endpoint' => 'block', 'method' => 'GET', 'form' => ['users_id' => 9]], true, 'Method not allowed'],
            'JSON user report' => [['endpoint' => 'report', 'body' => ['reported_users_id' => 9, 'obs' => 'Reason'], 'mailSent' => true], false],
            'lost user report' => [['endpoint' => 'report', 'body' => ['reported_users_id' => 9]], true, 'Could not send the report. Please try again later'],
            'comment report' => [['endpoint' => 'report', 'body' => ['comments_id' => 12], 'mailSent' => true], false],
            'lost comment report' => [['endpoint' => 'report', 'body' => ['comments_id' => 12]], true, 'Could not send the report. Please try again later'],
            'mail exception' => [['endpoint' => 'report', 'body' => ['reported_users_id' => 9], 'throwMail' => true], true, 'An error occurred'],
            'hidden video report' => [['endpoint' => 'report', 'body' => ['videos_id' => 91], 'hidden' => true], true, 'Cannot watch video', true],
            'hidden comment report' => [['endpoint' => 'report', 'body' => ['comments_id' => 12], 'hidden' => true], true, 'Cannot watch video', true],
            'GET report' => [['endpoint' => 'report', 'method' => 'GET', 'form' => ['reported_users_id' => 9]], true, 'Method not allowed'],
            'missing user report' => [['endpoint' => 'report', 'body' => ['reported_users_id' => 404]], true, 'User not found'],
            'missing comment report' => [['endpoint' => 'report', 'body' => ['comments_id' => 404]], true, 'Comment not found'],
        ];
    }
}

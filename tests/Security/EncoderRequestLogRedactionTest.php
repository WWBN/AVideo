<?php

namespace Tests\Security\EncoderRequestLogRedaction;

use PHPUnit\Framework\TestCase;

$encoderLogSource = file_get_contents(dirname(__DIR__, 2) . '/objects/aVideoEncoder.json.php');
$encoderLogStart = strpos($encoderLogSource, 'function getEncoderRequestDebugSummary(');
$encoderLogEnd = strpos($encoderLogSource, "\$global['bypassSameDomainCheck'] = 1;", $encoderLogStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($encoderLogSource, $encoderLogStart, $encoderLogEnd - $encoderLogStart));
unset($encoderLogSource, $encoderLogStart, $encoderLogEnd);

class EncoderRequestLogRedactionTest extends TestCase
{
    public function testCallbackChunkCapabilityIsRedactedWithoutChangingRequest()
    {
        $savedRequest = $_REQUEST;
        try {
            $_REQUEST = ['videos_id' => 71, 'pass' => 'password-secret', 'encoderChunkToken' => 'top-level-chunk-secret',
                'return_vars' => json_encode(['videos_id' => 71, 'video_id_hash' => 'video-secret', 'encoderChunkToken' => 'chunk-secret'])];
            $original = $_REQUEST;
            $summary = getEncoderRequestDebugSummary();
            foreach (['password-secret', 'top-level-chunk-secret', 'video-secret', 'chunk-secret'] as $secret) {
                $this->assertStringNotContainsString($secret, $summary);
            }
            $this->assertSame($original, $_REQUEST);
            $this->assertSame(71, json_decode($summary, true)['return_vars']['videos_id']);
        } finally {
            $_REQUEST = $savedRequest;
        }
    }
}

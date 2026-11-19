<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PayPalWebhookDiagnosticTest extends TestCase
{
    public function testSafeDiagnosticsAndExistingHttpContract(): void
    {
        $directory = sys_get_temp_dir() . '/paypal-diagnostic-' . bin2hex(random_bytes(6));
        mkdir($directory . '/videos', 0700, true);
        $configuration = <<<'PHP'
<?php
class AVideoLog { static $ERROR=1; }
function _error_log(...$args) {}
function avideoDiagnosticLocation($file) { return basename($file); }
function avideoWriteRequestDiagnostic($event,$details) { $GLOBALS['diagnostic']=['event'=>$event,'details'=>$details]; }
class AVideoPlugin {
 static function loadPluginIfEnabled($name) { if (getenv('FIXTURE_MODE')==='load_failure') { throw new TypeError('PRIVATE-MARKER'); } return true; }
 static function getObjectData($name) { return (object)['currency'=>'USD']; }
}
class PayPalYPT {
 static function validateWebhook() {
  if (getenv('FIXTURE_MODE')==='verify_failure') { throw new RuntimeException('PRIVATE-MARKER'); }
  if (getenv('FIXTURE_MODE')==='invalid') { return false; }
  return (object)['webhook_event'=>(object)['event_type'=>'UNRELATED','id'=>'fixture','resource'=>(object)[]]];
 }
 static function recordDelivery(...$args) {}
 static function describeApiError($error) { return 'ApiError'; }
}
PHP;
        file_put_contents($directory . '/videos/configuration.php', $configuration);
        $program = '$global=["systemRootPath"=>$argv[1]."/"]; '
            . 'register_shutdown_function(function(){fwrite(STDERR,json_encode(["status"=>http_response_code()?:200,"diagnostic"=>$GLOBALS["diagnostic"]??null]));}); '
            . 'putenv("FIXTURE_MODE=".$argv[2]); require $argv[3];';
        try {
            foreach (['load_failure'=>500,'verify_failure'=>500,'invalid'=>400,'unrelated'=>200] as $mode=>$expected) {
                $process = proc_open([PHP_BINARY,'-r',$program,$directory,$mode,APP_ROOT.'/plugin/PayPalYPT/webhook.php'], [1=>['pipe','w'],2=>['pipe','w']], $pipes);
                $body=stream_get_contents($pipes[1]);$log=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
                $this->assertSame(0,proc_close($process),$log);
                $this->assertSame('',$body);
                $this->assertStringNotContainsString('PRIVATE-MARKER',$log);
                $record=json_decode($log,true,512,JSON_THROW_ON_ERROR);
                $this->assertSame($expected,$record['status']);
                if ($expected===500) {
                    $this->assertSame('paypal_webhook_error',$record['diagnostic']['event']);
                    $this->assertSame(['class','file','line'],array_keys($record['diagnostic']['details']));
                    $this->assertGreaterThan(0,$record['diagnostic']['details']['line']);
                } else { $this->assertNull($record['diagnostic']); }
            }
        } finally { unlink($directory.'/videos/configuration.php');rmdir($directory.'/videos');rmdir($directory); }
    }
}

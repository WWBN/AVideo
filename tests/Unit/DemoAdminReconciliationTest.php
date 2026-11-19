<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;

class DemoAdminReconciliationTest extends TestCase
{
    public function testValidatorsExistAndPreserveTheRuntimeFfmpegGuard(): void
    {
        require_once APP_ROOT.'/admin/functions.php';
        $this->assertSame('validateAdminFFmpegParametersSetting',getAdminPluginFieldValidators()['CustomizeAdvanced']['ffmpegParameters']);
        $plugin=(object)['text'=>'before','enabled'=>true,'ffmpegParameters'=>'-c:v libx264'];
        $saved=applyAdminPluginValues($plugin,['text'=>'after'],['text'],'Example');
        $this->assertSame('after',$saved->text);$this->assertTrue($saved->enabled);
        $this->assertSame('before',$plugin->text);
        $error='';$this->assertTrue(validateAdminFFmpegParametersSetting('-c:v libx264 -crf 23',$error));
        foreach (['-i https://example.invalid/input','-c:v libx264; command','-crf 99', ['-crf','23']] as $value) {
            $error='';$this->assertFalse(validateAdminFFmpegParametersSetting($value,$error));
            $this->assertSame('Use supported FFmpeg output option/value pairs',$error);
        }
        $saved=applyAdminPluginValues($plugin,['ffmpegParameters'=>'-c:v libx264 -crf 23'],['ffmpegParameters'],'CustomizeAdvanced');
        $this->assertSame('-c:v libx264 -crf 23',$saved->ffmpegParameters);
        $this->expectException(\InvalidArgumentException::class);
        applyAdminPluginValues($plugin,['ffmpegParameters'=>'-i https://example.invalid/input'],['ffmpegParameters'],'CustomizeAdvanced');
    }
}

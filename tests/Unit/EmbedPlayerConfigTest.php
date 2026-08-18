<?php

namespace Tests\Unit;

use Tests\TestCase;

class EmbedPlayerConfigTest extends TestCase
{
    private $originalGet;
    private $originalRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalGet = $_GET;
        $this->originalRequest = $_REQUEST;
        $_GET = [];
        $_REQUEST = [];
        require_once \APP_ROOT . '/objects/EmbedPlayerConfig.php';
    }

    protected function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_REQUEST = $this->originalRequest;
        parent::tearDown();
    }

    public function testNewVisibilityOptionsGenerateTheExpectedCssAndJs()
    {
        $_GET = $_REQUEST = [
            'disableCloseButton' => '1',
            'disableOwnerImage' => '1',
        ];

        $config = new \EmbedPlayerConfig();

        $this->assertTrue($config->isCloseButtonDisabled());
        $this->assertTrue($config->isOwnerImageDisabled());
        $this->assertStringContainsString('#CloseButtonInVideo', $config->getCustomCSS());
        $this->assertStringContainsString('#topInfo img', $config->getCustomCSS());
        $this->assertStringNotContainsString('addCloseButtonInVideo', $config->getCustomJS());
    }

    public function testShowInfoAndModestBrandingHaveObservableEffects()
    {
        $_GET = $_REQUEST = ['showinfo' => '0', 'modestbranding' => '1'];

        $config = new \EmbedPlayerConfig();

        $this->assertTrue($config->isEmbedTopInfoDisabled());
        $this->assertStringContainsString('.player-logo', $config->getCustomCSS());
        $this->assertArrayHasKey('showinfo', \EmbedPlayerConfig::getFieldsMetadata());
        $this->assertArrayNotHasKey('showInfo', \EmbedPlayerConfig::getFieldsMetadata());
    }

    public function testObjectFitAcceptsOnlySupportedCssValues()
    {
        $_GET = $_REQUEST = ['objectFit' => 'cover'];
        $valid = new \EmbedPlayerConfig();
        $this->assertSame('object-fit: cover', $valid->getObjectFit());
        $this->assertTrue($valid->validate());

        $_GET = $_REQUEST = ['objectFit' => 'invalid-value'];
        $invalid = new \EmbedPlayerConfig();
        $this->assertSame('', $invalid->getObjectFit());
    }

    public function testStartTimeCannotBeNegative()
    {
        $_GET = $_REQUEST = ['t' => '-30'];

        $config = new \EmbedPlayerConfig();

        $this->assertSame(0, $config->getStartTime());
        $this->assertTrue($config->validate());
    }

    public function testCloseOnEndDoesNotDependOnAVisibleCloseButton()
    {
        $_GET = $_REQUEST = [
            'disableCloseButton' => '1',
            'closeOnEnd' => '1',
        ];

        $config = new \EmbedPlayerConfig();
        $js = $config->getCustomJS();

        $this->assertStringContainsString('closeFullScreenOrHistoryBack()', $js);
        $this->assertStringNotContainsString("$('#CloseButtonInVideo')", $js);
    }
}

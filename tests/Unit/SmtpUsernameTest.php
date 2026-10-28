<?php
namespace Tests\Unit\SmtpUsername;

use PHPUnit\Framework\TestCase;

class ObjectYPT {}
$configurationSource = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/objects/configuration.php'));
$configurationStart = strpos($configurationSource, 'class AVideoConf extends ObjectYPT');
$configurationEnd = strrpos($configurationSource, "\n}\n}") + 2;
eval('namespace ' . __NAMESPACE__ . ';' . substr($configurationSource, $configurationStart, $configurationEnd - $configurationStart));

class SmtpUsernameTest extends TestCase
{
    public function testExistingSettingsNormalizeTheUsernameWithoutChangingThePassword()
    {
        $reflection = new \ReflectionClass(AVideoConf::class);
        $config = $reflection->newInstanceWithoutConstructor();
        $username = $reflection->getProperty('smtpUsername');
        $username->setAccessible(true);
        $username->setValue($config, " \tmailbox@example.test\r\n");
        $config->setSmtpPassword(' secret with spaces ');
        $this->assertSame('mailbox@example.test', $config->getSmtpUsername());
        $this->assertSame(' secret with spaces ', $config->getSmtpPassword());
    }

    public function testSavedUsernameIsNormalizedAndAnEmptyLegacyValueIsSafe()
    {
        $reflection = new \ReflectionClass(AVideoConf::class);
        $config = $reflection->newInstanceWithoutConstructor();
        $this->assertSame('', $config->getSmtpUsername());
        $config->setSmtpUsername(' mailbox@example.test ');
        $this->assertSame('mailbox@example.test', $config->getSmtpUsername());
        $username = $reflection->getProperty('smtpUsername');
        $username->setAccessible(true);
        $this->assertSame('mailbox@example.test', $username->getValue($config));
    }
}

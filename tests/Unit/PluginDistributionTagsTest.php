<?php

namespace Tests\Unit;

require_once dirname(__DIR__) . '/fixtures/social-login-user.php';

use PHPUnit\Framework\TestCase;

/**
 * The Plugins manager shows Free for the plugins distributed with AVideo (tracked in this repository) and Premium for
 * the ones with their own repository, whatever Free/Premium tag a plugin declares itself.
 */
class PluginDistributionTagsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (class_exists('Tests\\Unit\\PluginDistributionFixture\\Plugin', false)) {
            return;
        }
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/plugin.php');
        if (!preg_match('/class PluginTags\s*\{.*?\n\}/s', $source, $tagsClass)) {
            throw new \RuntimeException('PluginTags class not found');
        }
        if (!preg_match('/public static \$corePluginDirs = \[.*?\];/s', $source, $coreDirs)) {
            throw new \RuntimeException('Plugin::$corePluginDirs not found');
        }
        $methods = \Tests\Security\SocialLoginFixtures\extractProductionMethods('objects/plugin.php', ['isCorePlugin', 'hasTag', 'getDistributionTags']);
        eval('namespace Tests\\Unit\\PluginDistributionFixture; ' . $tagsClass[0] . ' class Plugin { ' . $coreDirs[0] . $methods . ' }');
    }

    public function testCoreListMatchesThePluginFoldersTrackedByGit()
    {
        $root = dirname(__DIR__, 2);
        $output = [];
        $code = 1;
        @exec('git -C ' . escapeshellarg($root) . ' ls-files plugin 2>&1', $output, $code);
        if ($code !== 0 || empty($output)) {
            $this->markTestSkipped('git is not available');
        }
        $tracked = [];
        foreach ($output as $line) {
            $parts = explode('/', $line);
            if (count($parts) > 2) {
                $tracked[$parts[1]] = true;
            }
        }
        $tracked = array_keys($tracked);
        $core = \Tests\Unit\PluginDistributionFixture\Plugin::$corePluginDirs;
        sort($tracked);
        sort($core);
        $this->assertSame($tracked, $core, 'Update Plugin::$corePluginDirs in objects/plugin.php');
    }

    public function testDeclaredFreeOrPremiumTagIsReplacedByTheRealOne()
    {
        $tags = \Tests\Unit\PluginDistributionFixture\PluginTags::class;
        $plugin = \Tests\Unit\PluginDistributionFixture\Plugin::class;

        // a separate-repository plugin that still declares Free
        $result = $plugin::getDistributionTags('LoginLDAP', [$tags::$FREE, $tags::$LOGIN, 'update']);
        $this->assertSame([$tags::$PREMIUM, $tags::$LOGIN, 'update'], $result);

        // a core plugin that declares nothing, or Premium, gets Free; Deprecated is kept
        $this->assertSame([$tags::$FREE], $plugin::getDistributionTags('Permissions', []));
        $this->assertSame([$tags::$FREE, $tags::$LOGIN, $tags::$DEPRECATED], $plugin::getDistributionTags('LoginWordPress', [$tags::$PREMIUM, $tags::$LOGIN, $tags::$DEPRECATED]));
        $this->assertTrue($plugin::hasTag($plugin::getDistributionTags('LoginWordPress', [$tags::$DEPRECATED]), $tags::$DEPRECATED));
        $this->assertFalse($plugin::hasTag($plugin::getDistributionTags('Live', [$tags::$LIVE]), $tags::$DEPRECATED));

        // a third-party or premium folder is never Free
        $this->assertFalse($plugin::isCorePlugin('VideoHLS'));
        $this->assertFalse($plugin::isCorePlugin('live'));
        $this->assertTrue($plugin::isCorePlugin('Live'));
    }
}

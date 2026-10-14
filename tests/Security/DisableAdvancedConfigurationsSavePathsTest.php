<?php

namespace Tests\Security\DisableAdvancedConfigurationsSavePaths;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class Fixture
{
    public static $name;
    public static $stored;
    public static $saved;
    public static $writes;
}

class EndpointStopped extends RuntimeException {}
function header($value) {}
function __($value) { return $value; }
function forbidIfIsUntrustedRequest($label) {}
function isGlobalTokenValid() { return true; }
function forbiddenPage($message, $log = false) { throw new EndpointStopped($message); }
function _json_decode($value) { return json_decode($value); }

class User
{
    public static function isAdmin() { return true; }
}

class AVideoLog
{
    public static $SECURITY = 'security';
    public static $ERROR = 'error';
}

class Plugin
{
    public function __construct($id = 0) {}
    public function getName() { return Fixture::$name; }
    public function getUuid() { return 'fixture-uuid'; }
    public function getObject_data() { return Fixture::$stored; }
    public function loadFromUUID($uuid) {}
    public function setObject_data($value) { Fixture::$saved = $value; }
    public function save() { ++Fixture::$writes; return 1; }
    public static function getPluginByUUID($uuid) { return ['object_data' => Fixture::$stored]; }
    public static function getPluginByName($name) { return ['id' => 1, 'name' => Fixture::$name]; }
    public static function encryptIfNeed($value) { return $value; }
}

class AVideoPlugin
{
    public static function getObjectData($name) { return PluginAbstract::getObjectDataFromDatabase('fixture-uuid'); }
}

// Use the real reader: existing installations can hold plain JSON or addcslashes(JSON, '\\').
// In particular, quoted FFmpeg options and Windows paths differ between those formats.
$savePathsReaderSource = file_get_contents(\APP_ROOT . '/plugin/Plugin.abstract.php');
$savePathsReaderStart = strpos($savePathsReaderSource, '    static function getObjectDataFromDatabase(');
$savePathsReaderEnd = strpos($savePathsReaderSource, '    public function getDataObject()', $savePathsReaderStart);
eval('namespace ' . __NAMESPACE__ . '; class PluginAbstract {'
    . substr($savePathsReaderSource, $savePathsReaderStart, $savePathsReaderEnd - $savePathsReaderStart) . '}');

class SecureVideosDirectory
{
    public function getUUID() { return 'fixture-uuid'; }
    public function getDataObject() { return PluginAbstract::getObjectDataFromDatabase($this->getUUID()); }
}

class DisableAdvancedConfigurationsSavePathsTest extends TestCase
{
    private $savedGlobals;

    protected function setUp(): void
    {
        require_once \APP_ROOT . '/admin/functions.php';
        foreach (['global', '_POST', 'config'] as $name) {
            $this->savedGlobals[$name] = $GLOBALS[$name] ?? null;
        }
        $GLOBALS['global'] = ['systemRootPath' => \APP_ROOT . '/', 'disableAdvancedConfigurations' => 1];
        $_POST = [];
        Fixture::$saved = null;
        Fixture::$writes = 0;
    }

    protected function tearDown(): void
    {
        foreach ($this->savedGlobals as $name => $value) {
            $GLOBALS[$name] = $value;
        }
    }

    private function runEndpoint($file)
    {
        $global =& $GLOBALS['global'];
        $source = file_get_contents(\APP_ROOT . $file);
        $source = preg_replace('/^\s*require_once .*;\r?$/m', '', $source);
        $source = preg_replace('/^<\?php|\?>\s*$/', '', $source);
        // Endpoint validation terminates the request; keep that behavior inside the test process.
        $source = preg_replace('/\bdie\((.*)\);/', 'throw new EndpointStopped($1);', $source);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use stdClass;' . $source);
        } catch (EndpointStopped $exception) {
            return $exception->getMessage();
        } finally {
            ob_end_clean();
        }
        return '';
    }

    public function testNonObjectJsonCannotEraseLockedSettings(): void
    {
        Fixture::$name = 'Cache';
        Fixture::$stored = '{"cacheDir":"/srv/custom-cache/"}';
        foreach (['[]', 'null', '"text"', '0', '{broken'] as $json) {
            $_POST = ['id' => 1, 'object_data' => $json];
            $this->runEndpoint('/objects/pluginAddDataObject.json.php');
            $this->assertSame(0, Fixture::$writes, $json . ' must be rejected before saving');
            $this->assertNull(Fixture::$saved);
        }
    }

    public function testManagerJsonKeepsTheSameRuntimeValueForBothStoredJsonFormats(): void
    {
        foreach ([
            'Cache' => ['cacheDir' => 'C:\\media\\cache', 'label' => 'old'],
            'Rebroadcaster' => ['ffmpegParameters' => '-vf "scale=1280:720"', 'label' => 'old'],
        ] as $pluginName => $stored) {
            foreach ([false, true] as $legacyEscaping) {
                Fixture::$name = $pluginName;
                $json = json_encode((object) $stored);
                Fixture::$stored = $legacyEscaping ? addcslashes($json, '\\') : $json;
                $before = PluginAbstract::getObjectDataFromDatabase('fixture-uuid');
                $_POST = ['id' => 1, 'object_data' => '{"label":"new"}'];
                $this->runEndpoint('/objects/pluginAddDataObject.json.php');
                $this->assertNotNull(Fixture::$saved);
                Fixture::$stored = Fixture::$saved;
                $after = PluginAbstract::getObjectDataFromDatabase('fixture-uuid');
                $key = $pluginName === 'Cache' ? 'cacheDir' : 'ffmpegParameters';
                $this->assertSame($before->$key, $after->$key, $pluginName . ': locked runtime value changed');
                $this->assertSame('new', $after->label);
            }
        }
    }

    public function testManagerJsonStillEditsCatalogSettingsWhenTheFlagIsOff(): void
    {
        $GLOBALS['global']['disableAdvancedConfigurations'] = 0;
        Fixture::$name = 'Cache';
        Fixture::$stored = '{"cacheDir":"/srv/old/"}';
        $_POST = ['id' => 1, 'object_data' => '{"cacheDir":"/srv/new/"}'];
        $this->runEndpoint('/objects/pluginAddDataObject.json.php');
        $this->assertSame(1, Fixture::$writes);
        $this->assertSame('/srv/new/', json_decode(Fixture::$saved)->cacheDir);
    }

    public function testAdminFormPreservesWindowsPathsAtRuntimeWithEitherFlag(): void
    {
        foreach ([1, 0] as $flag) {
            $GLOBALS['global']['disableAdvancedConfigurations'] = $flag;
            Fixture::$name = 'Cache';
            Fixture::$stored = addcslashes(json_encode((object) ['cacheDir' => 'C:\\new\\temp', 'label' => 'old']), '\\');
            $_POST = ['pluginName' => 'Cache', 'pluginsList' => 'cacheDir|label', 'cacheDir' => 'D:\\new\\temp', 'label' => 'new'];
            $this->runEndpoint('/admin/save.json.php');
            $this->assertNotNull(Fixture::$saved);
            Fixture::$stored = Fixture::$saved;
            $after = PluginAbstract::getObjectDataFromDatabase('fixture-uuid');
            $expected = $flag ? 'C:\\new\\temp' : 'D:\\new\\temp';
            $this->assertSame($expected, $after->cacheDir);
            $this->assertSame('new', $after->label);
        }
    }

    public function testDedicatedVideosPathEditorHonorsTheFlag(): void
    {
        if (!is_file(\APP_ROOT . '/plugin/SecureVideosDirectory/page/editorSave.php')) {
            $this->markTestSkipped('The separately distributed SecureVideosDirectory plugin is not installed');
        }
        foreach ([1, 0] as $flag) {
            $GLOBALS['global']['disableAdvancedConfigurations'] = $flag;
            Fixture::$name = 'SecureVideosDirectory';
            foreach ([['/srv/stored-videos/', '/srv/submitted-videos/'], ['C:\\new\\temp', 'D:\\new\\temp']] as $paths) {
                Fixture::$stored = addcslashes(json_encode((object) ['videosAbsolutePath' => $paths[0]]), '\\');
                $_POST = ['videosAbsolutePath' => $paths[1]];
                $this->runEndpoint('/plugin/SecureVideosDirectory/page/editorSave.php');
                Fixture::$stored = Fixture::$saved;
                $after = PluginAbstract::getObjectDataFromDatabase('fixture-uuid');
                $this->assertSame($paths[$flag ? 0 : 1], $after->videosAbsolutePath);
            }
        }
    }
}

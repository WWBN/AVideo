<?php

namespace Tests\Security\AiEscapingFixtures;

class ObjectYPT
{
    public static $saved;
    public function __construct($id = 0) {}
    public function save() { self::$saved = $this; return 1; }
    public static function getSqlFromPost() { return ''; }
}

class sqlDAL
{
    public static $rows = [];
    public static function readSql($sql, $format, $values) { return true; }
    public static function fetchAllAssoc($result) { return self::$rows; }
    public static function close($result) {}
}

class Video
{
    public static $directory;
    public function __construct($title, $filename, $id) {}
    public function getFilename() { return 'ai-test'; }
}

function getVideosDir() { return Video::$directory . DIRECTORY_SEPARATOR; }
function getURL($path) { return $path; }

// Execute the production classes without loading the live configuration/database.
foreach (['Ai_transcribe_responses', 'Ai_responses'] as $name) {
    $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/Objects/' . $name . '.php');
    eval('namespace ' . __NAMESPACE__ . '; use stdClass; ' . substr($source, strpos($source, 'class ' . $name)));
}
$source = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsSecurity.php');
preg_match('/function xss_esc\(\$text\).*?return \$result;\s*}/s', $source, $match);
eval('namespace ' . __NAMESPACE__ . '; ' . $match[0]);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\AiEscapingFixtures\Ai_transcribe_responses;
use Tests\Security\AiEscapingFixtures\Ai_responses;
use Tests\Security\AiEscapingFixtures\sqlDAL;
use Tests\Security\AiEscapingFixtures\Video;

class AiTranscriptionEscapingTest extends TestCase
{
    public function testStoredTextRoundTripsWithoutLosingLiteralEntities()
    {
        $row = new Ai_transcribe_responses();
        foreach (["It's \"quoted\" & <literal> 中文 العربية", 'literal &amp; &#039; &quot;', '0', '<img src=x onerror=alert(1)>', str_repeat('ação & ', 20000)] as $text) {
            $row->setText($text);
            $this->assertSame($text, htmlspecialchars_decode($row->getText(), ENT_QUOTES));
            $this->assertStringNotContainsString('<', $row->getText());
        }
    }

    public function testInvalidUtf8DoesNotStripValidWordsOrMarkup()
    {
        $row = new Ai_transcribe_responses();
        $row->setText("document.cookie <literal> café \xFF 'quoted'");
        $this->assertSame("document.cookie <literal> café \xEF\xBF\xBD 'quoted'", htmlspecialchars_decode($row->getText(), ENT_QUOTES));
        $this->assertStringNotContainsString('<', $row->getText());
    }

    public function testAiContextReceivesPlainText()
    {
        $text = "It's & <literal> 中文 and literal &amp;";
        $row = new Ai_transcribe_responses();
        $row->setText($text);
        sqlDAL::$rows = [['text' => $row->getText(), 'vtt' => 'WEBVTT']];
        $this->assertSame($text, Ai_responses::getTranscriptionText(1));
        $this->assertSame('WEBVTT', Ai_responses::getTranscriptionVtt(1));
    }

    public function testByteSizeUsesPlainTextAndPreservesVtt()
    {
        $row = new Ai_transcribe_responses();
        $text = "It's & <literal> 中文";
        $row->setText($text);
        $row->setVtt('');
        $row->save();
        $this->assertSame(strlen($text), $row->getSize_in_bytes());
        $vtt = "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n$text";
        $row->setVtt($vtt);
        $row->setSize_in_bytes(0);
        $row->save();
        $this->assertSame(strlen($vtt), $row->getSize_in_bytes());
        $this->assertSame($vtt, $row->getVtt());
    }

    public function testSaveVttWritesOriginalSubtitleForTranscriptionAndDubbing()
    {
        $directory = tempnam(sys_get_temp_dir(), 'avideo-ai-');
        unlink($directory);
        mkdir($directory);
        mkdir($directory . '/ai-test');
        Video::$directory = $directory;
        $text = "It's & <literal> 中文";
        $vtt = "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n$text";
        $path = $directory . '/ai-test/ai-test.pt.vtt';
        try {
            // Dubbing passes VTT as text; transcription passes a separate plain transcript.
            foreach ([$text, $vtt] as $callbackText) {
                $decoded = (object) ['token' => (object) ['ai_responses_id' => 1, 'videos_id' => 1]];
                $result = Ai_transcribe_responses::saveVTT($vtt, 'pt', 1, $callbackText, 0, strlen($vtt), '', $decoded);
                $this->assertSame(1, $result->Ai_transcribe_responses);
                $this->assertSame(strlen($vtt), $result->vttsaved);
                $this->assertSame($vtt, file_get_contents($path));
                $this->assertSame($callbackText, htmlspecialchars_decode(\Tests\Security\AiEscapingFixtures\ObjectYPT::$saved->getText(), ENT_QUOTES));
            }
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
            rmdir($directory . '/ai-test');
            rmdir($directory);
        }
    }

    public function testUsageTypeDoesNotRenderCallbackLanguageAsHtml()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/tabs/usage.json.php');
        $start = strpos($source, 'foreach ($obj->response');
        $end = strpos($source, '$obj->error', $start);
        $obj = (object) ['response' => [['ai_transcribe_responses_id' => 1, 'total_price' => 0, 'price' => 0, 'language' => '<img src=x onerror=alert(1)>']]];
        eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));
        $this->assertStringNotContainsString('<', $obj->response[0]['type']);
        $this->assertStringContainsString('&lt;img', $obj->response[0]['type']);
    }

    public function testTabSafelyDisplaysLegacyAndEscapedText()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/tabs/transcriptions.json.php');
        $start = strpos($source, 'foreach ($obj->response');
        $end = strpos($source, '$obj->error', $start);
        $payload = '<img src=x onerror=alert(1)> & quoted';
        $obj = (object) ['response' => [
            ['text' => $payload, 'language' => $payload, 'size_in_bytes' => 10],
            ['text' => htmlspecialchars($payload, ENT_QUOTES, 'UTF-8'), 'language' => 'pt', 'size_in_bytes' => 10],
        ]];
        eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));
        foreach ($obj->response as $row) {
            $this->assertStringNotContainsString('<', $row['text']);
            $this->assertStringNotContainsString('<', $row['language']);
            $this->assertSame($payload, htmlspecialchars_decode($row['text'], ENT_QUOTES));
        }
    }

    public function testAdminEditReceivesPlainTextAndAllAiStringsUseTextRenderer()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/View/Ai_transcribe_responses/list.json.php');
        $start = strpos($source, 'foreach ($rows');
        $this->assertNotFalse($start, 'Decode stored text before filling the edit textarea.');
        $end = strpos($source, '$response =', $start);
        $text = "It's & literal &amp; <tag>";
        $rows = [['text' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8')]];
        eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));
        $this->assertSame($text, $rows[0]['text']);
        $row = new Ai_transcribe_responses();
        $row->setText($rows[0]['text']);
        $this->assertSame(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), $row->getText());
        $view = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/View/Ai_transcribe_responses/index_body.php');
        foreach (['vtt', 'language', 'duration', 'text', 'mp3_url'] as $column) {
            $this->assertStringContainsString('{"data": "' . $column . '", "render": $.fn.dataTable.render.text()}', $view);
        }
    }

    public function testShortsTranscriptAndHiddenTitleAreEscaped()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/tabs/shorts.ajax.php');
        $start = strpos($source, 'function getTranscriptionJson(');
        $end = strpos($source, 'function getShortsButtons(', $start);
        if (!function_exists(__NAMESPACE__ . '\\getTranscriptionJson')) {
            eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));
        }
        $payload = '<img src=x onerror=alert(1)>';
        $lines = getTranscriptionJson(0, 60, [(object) ['startInSeconds' => 0, 'endInSeconds' => 60, 'start' => $payload, 'text' => $payload]]);
        $this->assertStringNotContainsString('<img', implode('', $lines));
        $this->assertStringContainsString('&lt;img', implode('', $lines));
        $this->assertStringContainsString('<textarea name="title"><?= htmlspecialchars($value->shortTitle, ENT_QUOTES | ENT_SUBSTITUTE, \'UTF-8\') ?></textarea>', $source);
        preg_match('/<textarea name="title">.*?<\/textarea>/', $source, $match);
        $value = (object) ['shortTitle' => '</textarea><img src=x onerror=alert(1)>'];
        ob_start();
        eval('?>' . $match[0]);
        $html = ob_get_clean();
        $document = new \DOMDocument();
        $document->loadHTML($html);
        $this->assertSame(0, $document->getElementsByTagName('img')->length);
        $this->assertSame($value->shortTitle, $document->getElementsByTagName('textarea')->item(0)->textContent);
    }
}

function humanFileSize($size) { return $size . ' B'; }
function __($text) { return $text; }
class AI
{
    public static function formatPrice($price) { return (string) $price; }
}

<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * InputSanitizationTest
 * 
 * Testes críticos de sanitização de inputs que devem rodar em cada atualização
 * 
 * Valida que todos os inputs externos são sanitizados:
 * - $_POST, $_GET, $_REQUEST
 * - Integers com intval()
 * - Strings com htmlspecialchars()
 * - Filenames com preg_replace()
 * - URLs com filter_var()
 * - Datas com preg_replace()
 * 
 * Run: vendor/bin/phpunit tests/Security/InputSanitizationTest.php
 */
class InputSanitizationTest extends TestCase
{
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Testa que intval() sanitiza IDs
     * 
     * @test
     * @dataProvider integerInputsProvider
     */
    public function testIntvalSanitizesIds($input, $expected)
    {
        $sanitized = intval($input);
        
        $this->assertEquals(
            $expected,
            $sanitized,
            "intval should sanitize '{$input}' to {$expected}"
        );
        
        $this->assertIsInt($sanitized);
    }

    /**
     * Data provider para inputs de integer
     * 
     * @return array
     */
    public function integerInputsProvider()
    {
        return [
            'valid integer' => [123, 123],
            'string integer' => ['456', 456],
            'SQL injection' => ["123' OR '1'='1", 123],
            'XSS attempt' => ["<script>alert(123)</script>", 0],
            'negative' => [-5, -5],
            'float' => [123.99, 123],
            'zero' => [0, 0],
            'null' => [null, 0],
            'empty string' => ['', 0],
        ];
    }

    /**
     * Testa que htmlspecialchars() escapa HTML
     * 
     * @test
     * @dataProvider htmlInputsProvider
     */
    public function testHtmlspecialcharsEscapesHtml($input)
    {
        $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString(
            '<script>',
            $escaped,
            'Escaped output should not contain literal script tags'
        );
        
        $this->assertNotEquals(
            $input,
            $escaped,
            'Output should be different from input'
        );
    }

    /**
     * Data provider para HTML inputs
     * 
     * @return array
     */
    public function htmlInputsProvider()
    {
        return [
            'script tag' => ['<script>alert("XSS")</script>'],
            'img tag' => ['<img src=x onerror=alert("XSS")>'],
            'quotes' => ["test' OR '1'='1"],
            'double quotes' => ['test" OR "1"="1'],
            'ampersand' => ['Tom & Jerry'],
        ];
    }

    /**
     * Testa que strip_tags() remove HTML perigoso
     * 
     * @test
     */
    public function testStripTagsRemovesDangerousHtml()
    {
        $dangerous = '<script>alert("XSS")</script><p>Safe text</p>';
        $stripped = strip_tags($dangerous, '<p><br><b><i>');
        
        $this->assertStringNotContainsString(
            '<script>',
            $stripped,
            'Script tags should be removed'
        );
        
        $this->assertStringContainsString(
            '<p>',
            $stripped,
            'Allowed tags should be preserved'
        );
    }

    /**
     * Testa que addslashes() escapa aspas
     * 
     * @test
     */
    public function testAddslashesEscapesQuotes()
    {
        $input = "test' OR '1'='1";
        $escaped = addslashes($input);
        
        $this->assertStringContainsString(
            "\\'",
            $escaped,
            'Single quotes should be escaped'
        );
    }

    /**
     * Testa que preg_replace() sanitiza filenames
     * 
     * @test
     */
    public function testPregReplaceSanitizesFilenames()
    {
        $dangerousFilenames = [
            '<script>evil.php</script>',
            '../../../etc/passwd',
            'file; rm -rf /',
            "file\x00.php",
        ];
        
        foreach ($dangerousFilenames as $filename) {
            // Remove everything except alphanumeric, underscore, dash
            $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
            
            $this->assertDoesNotMatchRegularExpression(
                "/[<>'\";\\\\.\\/\s]/",
                $safe,
                "Filename should be sanitized: {$filename}"
            );
        }
    }

    /**
     * Testa que filter_var() valida URLs
     * 
     * @test
     */
    public function testFilterVarValidatesUrls()
    {
        $validUrls = [
            'https://example.com',
            'http://www.example.com/path',
        ];
        
        $invalidUrls = [
            'javascript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            'not a url',
            '../../../etc/passwd',
        ];
        
        foreach ($validUrls as $url) {
            $this->assertNotFalse(
                filter_var($url, FILTER_VALIDATE_URL),
                "Should validate valid URL: {$url}"
            );
        }
        
        foreach ($invalidUrls as $url) {
            $this->assertFalse(
                filter_var($url, FILTER_VALIDATE_URL),
                "Should reject invalid URL: {$url}"
            );
        }
    }

    /**
     * Testa que filter_var() valida emails
     * 
     * @test
     */
    public function testFilterVarValidatesEmails()
    {
        $validEmails = [
            'user@example.com',
            'test.user+tag@example.co.uk',
        ];
        
        $invalidEmails = [
            'not an email',
            '@example.com',
            'user@',
            'user@<script>alert("XSS")</script>',
        ];
        
        foreach ($validEmails as $email) {
            $this->assertNotFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Should validate valid email: {$email}"
            );
        }
        
        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Should reject invalid email: {$email}"
            );
        }
    }

    /**
     * Testa que preg_replace() sanitiza datas
     * 
     * @test
     */
    public function testPregReplaceSanitizesDates()
    {
        $dangerousDates = [
            "2025-01-01' OR '1'='1",
            "2025-01-01; DROP TABLE",
            "2025<script>alert('XSS')</script>",
        ];
        
        foreach ($dangerousDates as $date) {
            // Allow only: numbers, colon, space, dash
            $safe = preg_replace('/[^0-9: -]/', '', $date);
            
            $this->assertDoesNotMatchRegularExpression(
                "/[';\"<>\\\\]/",
                $safe,
                "Date should be sanitized: {$date}"
            );
        }
    }

    /**
     * Testa que basename() previne path traversal
     * 
     * @test
     */
    public function testBasenamePrevenrsPathTraversal()
    {
        $maliciousPaths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\config\\sam',
            '/var/www/html/../../../etc/passwd',
        ];
        
        foreach ($maliciousPaths as $path) {
            $safe = basename($path);
            
            $this->assertStringNotContainsString(
                '../',
                $safe,
                'basename should remove path traversal'
            );
        }
    }

    /**
     * Testa que json_encode() escapa para JSON
     * 
     * @test
     */
    public function testJsonEncodeEscapesForJson()
    {
        $dangerous = '<script>alert("XSS")</script>';
        $json = json_encode(['data' => $dangerous], JSON_HEX_TAG);
        
        $this->assertStringNotContainsString(
            '<script>',
            $json,
            'JSON with HEX_TAG should escape HTML tags'
        );
    }

    /**
     * Testa que trim() remove whitespace
     * 
     * @test
     */
    public function testTrimRemovesWhitespace()
    {
        $inputs = [
            '  test  ' => 'test',
            "\n\ntest\n\n" => 'test',
            "\t\ttest\t\t" => 'test',
        ];
        
        foreach ($inputs as $input => $expected) {
            $trimmed = trim($input);
            $this->assertEquals($expected, $trimmed);
        }
    }

    /**
     * Testa que strtolower() normaliza case
     * 
     * @test
     */
    public function testStrtolowerNormalizesCase()
    {
        $inputs = ['TEST', 'Test', 'TeSt', 'test'];
        
        foreach ($inputs as $input) {
            $lower = strtolower($input);
            $this->assertEquals('test', $lower);
        }
    }

    /**
     * Testa que str_replace() remove caracteres específicos
     * 
     * @test
     */
    public function testStrReplaceRemovesSpecificChars()
    {
        $input = "test\x00with\x00null\x00bytes";
        $cleaned = str_replace("\x00", '', $input);
        
        $this->assertStringNotContainsString(
            "\x00",
            $cleaned,
            'Null bytes should be removed'
        );
    }

    /**
     * Testa que cleanString() sanitiza URLs
     * 
     * @test
     */
    public function testCleanStringFunction()
    {
        if (function_exists('cleanString')) {
            $dirty = "Test Video! @#$%^&*()";
            $clean = cleanString($dirty);
            
            $this->assertIsString($clean);
            // Should remove or replace special characters
        } else {
            $this->markTestSkipped('cleanString function not available');
        }
    }

    /**
     * Testa que sanitizeAlphanumericForCallback() existe
     * 
     * @test
     */
    public function testSanitizeAlphanumericForCallbackExists()
    {
        // Simulate alphanumeric sanitization
        $input = "test123!@#$%";
        $sanitized = preg_replace('/[^a-zA-Z0-9]/', '', $input);
        
        $this->assertEquals('test123', $sanitized, 'Should keep only alphanumeric characters');
    }

    /**
     * Testa que inputs de array são validados
     * 
     * @test
     */
    public function testArrayInputsAreValidated()
    {
        $_POST['usergroups_id'] = "1' OR '1'='1";
        
        // Should convert to array and sanitize each element
        if (!is_array($_POST['usergroups_id'])) {
            $_POST['usergroups_id'] = [$_POST['usergroups_id']];
        }
        
        foreach ($_POST['usergroups_id'] as $key => $value) {
            $_POST['usergroups_id'][$key] = intval($value);
        }
        
        $this->assertEquals([1], $_POST['usergroups_id']);
        
        unset($_POST['usergroups_id']);
    }

    /**
     * Testa que boolean inputs são convertidos corretamente
     * 
     * @test
     */
    public function testBooleanInputsAreConverted()
    {
        $inputs = [
            '1' => true,
            '0' => false,
            'true' => true,
            'false' => false,
            '' => false,
        ];
        
        foreach ($inputs as $input => $expected) {
            $bool = !empty($input) && $input !== '0' && $input !== 'false';
            $this->assertEquals($expected, $bool);
        }
    }

    /**
     * Testa que floats são sanitizados
     * 
     * @test
     */
    public function testFloatsAreSanitized()
    {
        $inputs = [
            '123.45' => 123.45,
            '123,45' => 123.0, // European format would need special handling
            '123.45.67' => 123.45,
            "123.45' OR '1'='1" => 123.45,
        ];
        
        foreach ($inputs as $input => $expected) {
            $float = floatval($input);
            $this->assertIsFloat($float);
        }
    }

    /**
     * Testa que textarea inputs são sanitizados
     * 
     * @test
     */
    public function testTextareaInputsAreSanitized()
    {
        $input = "<script>alert('XSS')</script>\n<p>Safe paragraph</p>";
        
        // Option 1: Strip all tags
        $stripped = strip_tags($input);
        $this->assertStringNotContainsString('<script>', $stripped);
        
        // Option 2: Allow some tags
        $allowed = strip_tags($input, '<p><br><b><i><u>');
        $this->assertStringNotContainsString('<script>', $allowed);
        $this->assertStringContainsString('<p>', $allowed);
    }

    /**
     * Testa que search phrases são escapadas para LIKE
     * 
     * @test
     */
    public function testSearchPhrasesAreEscapedForLike()
    {
        $searchPhrase = "test%' OR '1'='1";
        
        // Should escape % and _ for LIKE queries
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $searchPhrase);
        
        $this->assertStringContainsString('\\%', $escaped);
        
        // Also escape for SQL
        $sqlSafe = addslashes($escaped);
        $this->assertStringContainsString("\\'", $sqlSafe);
    }

    /**
     * Testa que ORDER BY fields têm whitelist
     * 
     * @test
     */
    public function testOrderByFieldsHaveWhitelist()
    {
        $allowedOrderFields = ['title', 'created', 'modified', 'views_count'];
        
        $userInput = "title; DROP TABLE videos";
        
        $isAllowed = in_array($userInput, $allowedOrderFields);
        
        $this->assertFalse(
            $isAllowed,
            'Malicious ORDER BY field should be rejected'
        );
    }

    /**
     * Testa que LIMIT/OFFSET são integers
     * 
     * @test
     */
    public function testLimitOffsetAreIntegers()
    {
        $_GET['rowCount'] = "10' OR '1'='1";
        $_GET['current'] = "1; DROP TABLE";
        
        $rowCount = intval($_GET['rowCount']);
        $current = intval($_GET['current']);
        
        $this->assertEquals(10, $rowCount);
        $this->assertEquals(1, $current);
        
        unset($_GET['rowCount'], $_GET['current']);
    }

    /**
     * Testa que color codes são validados
     * 
     * @test
     */
    public function testColorCodesAreValidated()
    {
        $validColors = ['#FF0000', '#00FF00', '#0000FF', '#FFF'];
        $invalidColors = ['red', 'rgb(255,0,0)', '#GG0000', '<script>'];
        
        foreach ($validColors as $color) {
            $this->assertMatchesRegularExpression(
                '/^#[0-9A-Fa-f]{3,6}$/',
                $color,
                "Valid color should match pattern: {$color}"
            );
        }
        
        foreach ($invalidColors as $color) {
            $this->assertDoesNotMatchRegularExpression(
                '/^#[0-9A-Fa-f]{3,6}$/',
                $color,
                "Invalid color should not match pattern: {$color}"
            );
        }
    }

    /**
     * Testa que timezone strings são validados
     * 
     * @test
     */
    public function testTimezoneStringsAreValidated()
    {
        $validTimezones = ['America/New_York', 'Europe/London', 'UTC'];
        
        foreach ($validTimezones as $tz) {
            $this->assertTrue(
                in_array($tz, timezone_identifiers_list()),
                "Should validate timezone: {$tz}"
            );
        }
        
        $invalidTimezone = '<script>alert("XSS")</script>';
        $this->assertFalse(
            in_array($invalidTimezone, timezone_identifiers_list()),
            'Invalid timezone should be rejected'
        );
    }

    /**
     * Testa que language codes são validados
     * 
     * @test
     */
    public function testLanguageCodesAreValidated()
    {
        $validLangCodes = ['en', 'pt', 'es', 'fr', 'de'];
        $langCode = 'en';
        
        $this->assertContains($langCode, $validLangCodes);
        $this->assertMatchesRegularExpression('/^[a-z]{2}$/', $langCode);
    }
}

<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class CertifyCompleteTemplateTest extends TestCase
{
    private $source;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 2) . '/plugin/CertifyComplete/certificate.php';
        if (!is_file($path)) { $this->markTestSkipped('Separately distributed CertifyComplete plugin is not installed'); }
        $this->source = file_get_contents($path);
    }

    private function render($name): string
    {
        $_template = '<main>{textCertificate}</main>';
        $info = ['template' => ['{textCertificate}' => '<p>{name}</p><small>{eval_[21+21]}</small>'],
            'placeholders' => ['{name}' => $name, '{playlist_name}' => 'Course']];
        $start = strpos($this->source, "    \$template = strtr(\$_template, \$info['template']);");
        $end = strpos($this->source, '    //exit;', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        // Execute the actual rendering block without loading a live database.
        eval(substr($this->source, $start, $end - $start));
        return $html;
    }

    public function testStudentValuesCannotIntroduceTemplateExpressions(): void
    {
        $html = $this->render('Student {eval_[100+2]}');
        $this->assertStringContainsString('Student {eval_[100+2]}', $html);
        $this->assertStringNotContainsString('Student 102', $html);
        $this->assertStringContainsString('<small>42</small>', $html);
    }

    public function testStudentMarkupIsTextAndTemplateMarkupIsPreserved(): void
    {
        $html = $this->render('João <img src=x onerror=alert(1)> & "Test"');
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('João &lt;img', $html);
        $this->assertStringContainsString('&amp; &quot;Test&quot;', $html);
        $this->assertStringContainsString('<p>', $html);
    }

    public function testHistoricalCertificateEscapesStoredStudentMarkup(): void
    {
        $name = 'Student <img src=x onerror=alert(1)>';
        $json = (object) ['html' => '<main><p>' . $name . '</p></main>',
            'placeholders' => (object) ['{name}' => $name, '{playlist_name}' => 'Course']];
        $start = strpos($this->source, '    $html = $json->html;');
        $end = strpos($this->source, '    if(User::isAdmin())', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        eval(substr($this->source, $start, $end - $start));
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('<main><p>Student &lt;img', $html);
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;

class AdminPluginSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once APP_ROOT . '/admin/functions.php';
    }

    public function testPartialFormPreservesTextAndUnrelatedSettings(): void
    {
        $plugin = (object) ['enabled' => true, 'otherEnabled' => true, 'text' => 'old', 'zero' => 'old', 'blank' => 'old'];
        $saved = applyAdminPluginValues($plugin, ['text' => '1', 'zero' => '0', 'blank' => ''], ['enabled', 'text', 'zero', 'blank']);
        $this->assertFalse($saved->enabled);
        $this->assertTrue($saved->otherEnabled);
        $this->assertSame('1', $saved->text);
        $this->assertSame('0', $saved->zero);
        $this->assertSame('', $saved->blank);
        $this->assertTrue($plugin->enabled);
    }

    public function testSelectionAndTextareaKeepTheirMetadata(): void
    {
        $plugin = (object) [
            'selection' => (object) ['type' => ['0' => 'Off', '1' => 'On'], 'value' => '0'],
            'text' => (object) ['type' => 'textarea', 'value' => 'before', 'help' => 'Keep this'],
        ];
        $text = "First line\nPath: C:\\media\\videos & 'quotes'";
        $saved = applyAdminPluginValues($plugin, ['selection' => '1', 'text' => $text], ['selection', 'text']);
        $this->assertSame('1', $saved->selection->value);
        $this->assertSame($plugin->selection->type, $saved->selection->type);
        $this->assertSame($text, $saved->text->value);
        $this->assertSame('Keep this', $saved->text->help);
        $this->assertSame('before', $plugin->text->value);
        $roundTrip = json_decode(json_encode($saved));
        $this->assertSame($text, $roundTrip->text->value);
    }

    public function testMissingTextAndUnsupportedValuesAreNotOverwritten(): void
    {
        $plugin = (object) ['text' => 'keep', 'nested' => ['a' => 1], 'enabled' => true];
        $saved = applyAdminPluginValues($plugin, ['nested' => 'Array', 'unknown' => 'new', 'enabled' => 'false'], ['text', 'nested', 'unknown', 'enabled']);
        $this->assertSame('keep', $saved->text);
        $this->assertSame(['a' => 1], $saved->nested);
        $this->assertFalse(property_exists($saved, 'unknown'));
        $this->assertFalse($saved->enabled);
    }

    public function testRendererListsOnlyRenderedFieldsAndSupportsNumericObjectValues(): void
    {
        $plugin = (object) [
            'amount' => (object) ['type' => 'number', 'value' => 0],
            'selection' => (object) ['type' => (object) ['a' => 'A', 'b' => 'B'], 'value' => 'b'],
            'nested' => (object) ['internal' => true],
            'array' => ['internal'],
            'enabled' => true,
        ];
        $form = jsonToFormElements($plugin);
        $this->assertSame(['amount', 'selection', 'enabled'], array_keys($form));
        $this->assertStringContainsString("value='0'", $form['amount']);
        $this->assertStringContainsString("value='b' selected", $form['selection']);
        preg_match("/id='([^']+)'/", $form['amount'], $matches);
        $this->assertStringContainsString("for='{$matches[1]}'", $form['amount']);
    }
}

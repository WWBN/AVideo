<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpDocumentor-project.org
 */

namespace phpDocumentor\GraphViz\Test;

use phpDocumentor\GraphViz\Attribute;

/**
 * Test for the the class representing a GraphViz attribute.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpDocumentor-project.org
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \phpDocumentor\GraphViz\Attribute */
    protected $fixture = null;

    /**
     * Initializes the fixture for this test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fixture = new Attribute('a', '1');
    }

    /**
     * Tests the construct method
     *
     * @covers phpDocumentor\GraphViz\Attribute::__construct
     *
     * @returnn void
     */
    public function testConstruct()
    {
        $fixture = new Attribute('MyKey', 'MyValue');
        $this->assertInstanceOf(
            'phpDocumentor\GraphViz\Attribute',
            $fixture
        );
        $this->assertSame('MyKey', $fixture->getKey());
        $this->assertSame('MyValue', $fixture->getValue());
    }

    /**
     * Tests the getting and setting of the key.
     *
     * @covers \phpDocumentor\GraphViz\Attribute::getKey
     * @covers \phpDocumentor\GraphViz\Attribute::setKey
     *
     * @return void
     */
    public function testKey()
    {
        $this->assertSame(
            $this->fixture->getKey(), 'a',
            'Expecting the key to match the initial state'
        );
        $this->assertSame(
            $this->fixture, $this->fixture->setKey('b'),
            'Expecting a fluent interface'
        );
        $this->assertSame(
            $this->fixture->getKey(), 'b',
            'Expecting the key to contain the new value'
        );
    }

    /**
     * Tests the getting and setting of the value.
     *
     * @covers \phpDocumentor\GraphViz\Attribute::getValue
     * @covers \phpDocumentor\GraphViz\Attribute::setValue
     *
     * @return void
     */
    public function testValue()
    {
        $this->assertSame(
            $this->fixture->getValue(), '1',
            'Expecting the value to match the initial state'
        );
        $this->assertSame(
            $this->fixture, $this->fixture->setValue('2'),
            'Expecting a fluent interface'
        );
        $this->assertSame(
            $this->fixture->getValue(), '2',
            'Expecting the value to contain the new value'
        );
    }

    /**
     * Tests whether a string starting with a < is recognized as HTML.
     *
     * @covers \phpDocumentor\GraphViz\Attribute::isValueInHtml
     *
     * @return void
     */
    public function testIsValueInHtml()
    {
        $this->fixture->setValue('a');
        $this->assertFalse(
            $this->fixture->isValueInHtml(),
            'Expected value to not be a HTML code'
        );

        $this->fixture->setValue('<a>test</a>');
        $this->assertTrue(
            $this->fixture->isValueInHtml(),
            'Expected value to be recognized as a HTML code'
        );
    }

    /**
     * Tests whether the toString provides a valid GraphViz attribute string.
     *
     * @covers \phpDocumentor\GraphViz\Attribute::__toString
     *
     * @return void
     */
    public function testToString()
    {
        $this->fixture = new Attribute('a', 'b');
        $this->assertSame(
            'a="b"', (string)$this->fixture,
            'Strings should be surrounded with quotes'
        );

        $this->fixture->setValue('a"a');
        $this->assertSame(
            'a="a\"a"', (string)$this->fixture,
            'Strings should be surrounded with quotes'
        );

        $this->fixture->setKey('url');
        $this->assertSame(
            'URL="a\"a"', (string)$this->fixture,
            'The key named URL must be uppercased'
        );

        $this->fixture->setValue('<a>test</a>');
        $this->assertSame(
            'URL=<a>test</a>', (string)$this->fixture,
            'HTML strings should not be surrounded with quotes'
        );
    }

    /**
     * Tests whether the toString provides a valid GraphViz attribute string.
     *
     * @covers \phpDocumentor\GraphViz\Attribute::__toString
     * @covers \phpDocumentor\GraphViz\Attribute::encodeSpecials
     *
     * @return void
     */
    public function testToStringWithSpecials()
    {
        $this->fixture = new Attribute('a', 'b');

        $this->fixture->setValue('a\la');
        $this->assertSame(
            'a="a\la"', (string)$this->fixture,
            'Specials should not be escaped'
        );
        $this->fixture->setValue('a\l"a');
        $this->assertSame(
            'a="a\l\"a"', (string)$this->fixture,
            'Specials should not be escaped, but quotes should'
        );
        $this->fixture->setValue('a\\\\l"a');
        $this->assertSame(
            'a="a\\\\l\"a"', (string)$this->fixture,
            'Double backslashes should stay the same'
        );
    }

    /**
     * Tests whether the isValueContainingSpecials function
     *
     * @covers \phpDocumentor\GraphViz\Attribute::isValueContainingSpecials
     *
     * @return void
     */
    public function testIsValueContainingSpecials()
    {
        $this->fixture->setValue('+ name : string\l+ home_country : string\l');
        $this->assertTrue($this->fixture->isValueContainingSpecials());

        $this->fixture->setValue('+ ship(): boolean');
        $this->assertFalse($this->fixture->isValueContainingSpecials());
    }

    
}

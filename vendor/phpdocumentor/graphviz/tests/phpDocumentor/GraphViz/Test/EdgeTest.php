<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @package   phpDocumentor\GraphViz\Test
 * @author    Danny van der Sluijs <danny.vandersluijs@fleppuhstein.com>
 * @copyright 2012 Danny van der Sluijs (http://www.dannyvandersluijs.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpDocumentor-project.org
 */

namespace phpDocumentor\GraphViz\Test;

use phpDocumentor\GraphViz\Edge;
use phpDocumentor\GraphViz\Node;

/**
 * Test for the the class representing a GraphViz edge (vertex).
 *
 * @package phpDocumentor\GraphViz\Test
 * @author  Danny van der Sluijs <danny.vandersluijs@fleppuhstein.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpDocumentor-project.org
 */
class EdgeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var phpDocumentor\GraphViz\Edge
     */
    protected $fixture;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fixture = new Edge(new Node('from'), new Node('to'));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {

    }

    /**
     * Tests the construct method
     *
     * @covers phpDocumentor\GraphViz\Edge::__construct
     *
     * @return void
     */
    public function testConstruct()
    {
        $fromNode = $this->getMock('phpDocumentor\GraphViz\Node', array(), array(), '', false);
        $toNode = $this->getMock('phpDocumentor\GraphViz\Node', array(), array(), '', false);
        $fixture = new Edge($fromNode, $toNode);

        $this->assertInstanceOf(
            'phpDocumentor\GraphViz\Edge',
            $fixture
        );
        $this->assertSame(
            $fromNode,
            $fixture->getFrom()
        );
        $this->assertSame(
            $toNode,
            $fixture->getTo()
        );
    }

    /**
     * Tests the create method
     *
     * @covers phpDocumentor\GraphViz\Edge::create
     *
     * @return void
     */
    public function testCreate()
    {
        $this->assertInstanceOf(
            'phpDocumentor\GraphViz\Edge',
            Edge::create(new Node('from'), new Node('to'))
        );
    }

    /**
     * Tests whether the getFrom method returns the same node as passed
     * in the create method
     *
     * @covers phpDocumentor\GraphViz\Edge::getFrom
     *
     * @return void
     */
    public function testGetFrom()
    {
        $from = new Node('from');
        $edge = Edge::create($from, new Node('to'));
        $this->assertSame($from, $edge->getFrom());
    }

    /**
     * Tests the getTo method returns the same node as passed
     * in the create method
     *
     * @covers phpDocumentor\GraphViz\Edge::getTo
     *
     * @return void
     */
    public function testGetTo()
    {
        $to = new Node('to');
        $edge = Edge::create(new Node('from'), $to);
        $this->assertSame($to, $edge->getTo());
    }

    /**
     * Tests the magic __call method, to work as described, return the object
     * instance for a setX method, return the value for an getX method, and null
     * for the remaining method calls
     *
     * @covers phpDocumentor\GraphViz\Edge::__call
     *
     * @return void
     */
    public function testCall()
    {
        $label = 'my label';
        $this->assertInstanceOf('phpDocumentor\GraphViz\Edge', $this->fixture->setLabel($label));
        $this->assertSame($label, $this->fixture->getLabel()->getValue());
        $this->assertNull($this->fixture->someNonExcistingMethod());
    }

    /**
     * Tests whether the magic __toString method returns a well formatted string
     * as specified in the DOT standard
     *
     * @covers phpDocumentor\GraphViz\Edge::__toString
     *
     * @return void
     */
    public function testToString()
    {
        $this->fixture->setLabel('MyLabel');
        $this->fixture->setWeight(45);

        $dot = <<<DOT
"from" -> "to" [
label="MyLabel"
weight="45"
]
DOT;

        $this->assertSame($dot, (string) $this->fixture);
    }
}

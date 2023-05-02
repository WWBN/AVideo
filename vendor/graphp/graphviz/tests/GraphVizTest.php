<?php

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;
use PHPUnit\Framework\TestCase;

class GraphVizTest extends TestCase
{
    private $graphViz;

    public function setUp()
    {
        $this->graphViz = new GraphViz();
    }

    public function testGraphEmpty()
    {
        $graph = new Graph();

        $expected = <<<VIZ
graph {
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphWithName()
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.name', 'G');

                $expected = <<<VIZ
graph "G" {
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphWithNameWithSpaces()
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.name', 'My Graph Name');

                $expected = <<<VIZ
graph "My Graph Name" {
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphIsolatedVertices()
    {
        $graph = new Graph();
        $graph->createVertex('a');
        $graph->createVertex('b');

        $expected = <<<VIZ
graph {
  "a"
  "b"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphIsolatedVerticesWithGroupsWillBeAddedToClusters()
    {
        $graph = new Graph();
        $graph->createVertex('a')->setGroup(0);
        $graph->createVertex('b')->setGroup(1)->setAttribute('graphviz.label', 'second');

        $expected = <<<VIZ
graph {
  subgraph cluster_0 {
    label = 0
    "a"
  }
  subgraph cluster_1 {
    label = 1
    "b" [label="second"]
  }
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphDefaultAttributes()
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.bgcolor', 'transparent');
        $graph->setAttribute('graphviz.node.color', 'blue');
        $graph->setAttribute('graphviz.edge.color', 'grey');

        $expected = <<<VIZ
graph {
  graph [bgcolor="transparent"]
  node [color="blue"]
  edge [color="grey"]
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testUnknownGraphAttributesWillBeDiscarded()
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.vertex.color', 'blue');
        $graph->setAttribute('graphviz.unknown.color', 'red');

        $expected = <<<VIZ
graph {
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testEscaping()
    {
        $graph = new Graph();
        $graph->createVertex('a');
        $graph->createVertex('b¹²³ is; ok\\ay, "right"?');
        $graph->createVertex(3);
        $graph->createVertex(4)->setAttribute('graphviz.label', 'normal');
        $graph->createVertex(5)->setAttribute('graphviz.label', GraphViz::raw('<raw>'));


        $expected = <<<VIZ
graph {
  "a"
  "b¹²³ is; ok\\\\ay, &quot;right&quot;?"
  3
  4 [label="normal"]
  5 [label=<raw>]
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphWithSimpleEdgeUsesGraphWithSimpleEdgeDefinition()
    {
        // a -- b
        $graph = new Graph();
        $graph->createVertex('a')->createEdge($graph->createVertex('b'));

        $expected = <<<VIZ
graph {
  "a" -- "b"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphWithLoopUsesGraphWithSimpleLoopDefinition()
    {
        // a -- b -\
        //      |  |
        //      \--/
        $graph = new Graph();
        $graph->createVertex('a')->createEdge($graph->createVertex('b'));
        $graph->getVertex('b')->createEdge($graph->getVertex('b'));

        $expected = <<<VIZ
graph {
  "a" -- "b"
  "b" -- "b"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphDirectedUsesDigraph()
    {
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));

        $expected = <<<VIZ
digraph {
  "a" -> "b"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphDirectedWithLoopUsesDigraphWithSimpleLoopDefinition()
    {
        // a -> b -\
        //      ^  |
        //      \--/
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $graph->getVertex('b')->createEdgeTo($graph->getVertex('b'));

        $expected = <<<VIZ
digraph {
  "a" -> "b"
  "b" -> "b"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphMixedUsesDigraphWithExplicitDirectionNoneForUndirectedEdges()
    {
        // a -> b -- c
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $graph->createVertex('c')->createEdge($graph->getVertex('b'));

        $expected = <<<VIZ
digraph {
  "a" -> "b"
  "c" -> "b" [dir="none"]
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphMixedWithDirectedLoopUsesDigraphWithoutDirectionForDirectedLoop()
    {
        // a -- b -\
        //      ^  |
        //      \--/
        $graph = new Graph();
        $graph->createVertex('a')->createEdge($graph->createVertex('b'));
        $graph->getVertex('b')->createEdgeTo($graph->getVertex('b'));

        $expected = <<<VIZ
digraph {
  "a" -> "b" [dir="none"]
  "b" -> "b"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testGraphUndirectedWithIsolatedVerticesFirst()
    {
        // a -- b -- c   d
        $graph = new Graph();
        $graph->createVertices(array('a', 'b', 'c', 'd'));
        $graph->getVertex('a')->createEdge($graph->getVertex('b'));
        $graph->getVertex('b')->createEdge($graph->getVertex('c'));

        $expected = <<<VIZ
graph {
  "d"
  "a" -- "b"
  "b" -- "c"
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testVertexLabels()
    {
        $graph = new Graph();
        $graph->createVertex('a')->setBalance(1);
        $graph->createVertex('b')->setBalance(0);
        $graph->createVertex('c')->setBalance(-1);
        $graph->createVertex('d')->setAttribute('graphviz.label', 'test');
        $graph->createVertex('e')->setBalance(2)->setAttribute('graphviz.label', 'unnamed');

        $expected = <<<VIZ
graph {
  "a" [label="a (+1)"]
  "b" [label="b (0)"]
  "c" [label="c (-1)"]
  "d" [label="test"]
  "e" [label="unnamed (+2)"]
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testEdgeLayoutAtributes()
    {
        $graph = new Graph();
        $graph->createVertex('1a')->createEdge($graph->createVertex('1b'));
        $graph->createVertex('2a')->createEdge($graph->createVertex('2b'))->setAttribute('graphviz.numeric', 20);
        $graph->createVertex('3a')->createEdge($graph->createVertex('3b'))->setAttribute('graphviz.textual', "forty");
        $graph->createVertex('4a')->createEdge($graph->createVertex('4b'))->getAttributeBag()->setAttributes(array('graphviz.1' => 1, 'graphviz.2' => 2));
        $graph->createVertex('5a')->createEdge($graph->createVertex('5b'))->getAttributeBag()->setAttributes(array('graphviz.a' => 'b', 'graphviz.c' => 'd'));

        $expected = <<<VIZ
graph {
  "1a" -- "1b"
  "2a" -- "2b" [numeric=20]
  "3a" -- "3b" [textual="forty"]
  "4a" -- "4b" [1=1 2=2]
  "5a" -- "5b" [a="b" c="d"]
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testEdgeLabels()
    {
        $graph = new Graph();
        $graph->createVertex('1a')->createEdge($graph->createVertex('1b'));
        $graph->createVertex('2a')->createEdge($graph->createVertex('2b'))->setWeight(20);
        $graph->createVertex('3a')->createEdge($graph->createVertex('3b'))->setCapacity(30);
        $graph->createVertex('4a')->createEdge($graph->createVertex('4b'))->setFlow(40);
        $graph->createVertex('5a')->createEdge($graph->createVertex('5b'))->setFlow(50)->setCapacity(60);
        $graph->createVertex('6a')->createEdge($graph->createVertex('6b'))->setFlow(60)->setCapacity(70)->setWeight(80);
        $graph->createVertex('7a')->createEdge($graph->createVertex('7b'))->setFlow(70)->setAttribute('graphviz.label', 'prefixed');

        $expected = <<<VIZ
graph {
  "1a" -- "1b"
  "2a" -- "2b" [label=20]
  "3a" -- "3b" [label="0/30"]
  "4a" -- "4b" [label="40/∞"]
  "5a" -- "5b" [label="50/60"]
  "6a" -- "6b" [label="60/70/80"]
  "7a" -- "7b" [label="prefixed 70/∞"]
}

VIZ;

        $this->assertEquals($expected, $this->graphViz->createScript($graph));
    }

    public function testCreateImageSrcWillExportPngDefaultFormat()
    {
        $graph = new Graph();

        $src = $this->graphViz->createImageSrc($graph);

        $this->assertStringStartsWith('data:image/png;base64,', $src);
    }

    public function testCreateImageSrcAsSvgWithUtf8DefaultCharset()
    {
        $graph = new Graph();

        $this->graphViz->setFormat('svg');
        $src = $this->graphViz->createImageSrc($graph);

        $this->assertStringStartsWith('data:image/svg+xml;charset=UTF-8;base64,', $src);
    }

    public function testCreateImageSrcAsSvgzWithExplicitIsoCharsetLatin1()
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.charset', 'iso-8859-1');

        $this->graphViz->setFormat('svgz');
        $src = $this->graphViz->createImageSrc($graph);

        $this->assertStringStartsWith('data:image/svg+xml;charset=iso-8859-1;base64,', $src);
    }
}

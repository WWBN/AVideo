<?php

namespace Fhaculty\Graph;

use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Exception\InvalidArgumentException;
use Fhaculty\Graph\Set\DualAggregate;

/**
 * Base Walk class
 *
 * The general term "Walk" bundles the following mathematical concepts:
 * walk, path, cycle, circuit, loop, trail, tour, etc.
 *
 * @link http://en.wikipedia.org/wiki/Path_%28graph_theory%29
 * @link http://en.wikipedia.org/wiki/Glossary_of_graph_theory#Walks
 * @see Fhaculty\Graph\Algorithm\Property\WalkProperty for checking special cases, such as cycles, loops, closed trails, etc.
 */
class Walk implements DualAggregate
{
    /**
     * construct new walk from given start vertex and given array of edges
     *
     * @param  Edges|Edge[]         $edges
     * @param  Vertex               $startVertex
     * @return Walk
     */
    public static function factoryFromEdges($edges, Vertex $startVertex)
    {
        $vertices = array($startVertex);
        $vertexCurrent = $startVertex;
        foreach ($edges as $edge) {
            $vertexCurrent = $edge->getVertexToFrom($vertexCurrent);
            $vertices []= $vertexCurrent;
        }

        return new self($vertices, $edges);
    }

    /**
     * create new walk instance between given set of Vertices / array of Vertex instances
     *
     * @param  Vertices|Vertex[]  $vertices
     * @param  int|null           $by
     * @param  bool               $desc
     * @return Walk
     * @throws UnderflowException if no vertices were given
     * @see Edges::getEdgeOrder() for parameters $by and $desc
     */
    public static function factoryFromVertices($vertices, $by = null, $desc = false)
    {
        $edges = array();
        $last = NULL;
        foreach ($vertices as $vertex) {
            // skip first vertex as last is unknown
            if ($last !== NULL) {
                // pick edge between last vertex and this vertex
                /* @var $last Vertex */
                if ($by === null) {
                    $edges []= $last->getEdgesTo($vertex)->getEdgeFirst();
                } else {
                    $edges []= $last->getEdgesTo($vertex)->getEdgeOrder($by, $desc);
                }
            }
            $last = $vertex;
        }
        if ($last === NULL) {
            throw new UnderflowException('No vertices given');
        }

        return new self($vertices, $edges);
    }

    /**
     * create new cycle instance from given predecessor map
     *
     * @param  Vertex[]           $predecessors map of vid => predecessor vertex instance
     * @param  Vertex             $vertex       start vertex to search predecessors from
     * @param  int|null           $by
     * @param  bool               $desc
     * @return Walk
     * @throws UnderflowException
     * @see Edges::getEdgeOrder() for parameters $by and $desc
     * @uses self::factoryFromVertices()
     */
    public static function factoryCycleFromPredecessorMap(array $predecessors, Vertex $vertex, $by = null, $desc = false)
    {
        // find a vertex in the cycle
        $vid = $vertex->getId();
        $startVertices = array();
        do {
            if (!isset($predecessors[$vid])) {
                throw new InvalidArgumentException('Predecessor map is incomplete and does not form a cycle');
            }

            $startVertices[$vid] = $vertex;

            $vertex = $predecessors[$vid];
            $vid = $vertex->getId();
        } while (!isset($startVertices[$vid]));

        // find negative cycle
        $vid = $vertex->getId();
        // build array of vertices in cycle
        $vertices = array();
        do {
            // add new vertex to cycle
            $vertices[$vid] = $vertex;

            // get predecessor of vertex
            $vertex = $predecessors[$vid];
            $vid = $vertex->getId();
            // continue until we find a vertex that's already in the circle (i.e. circle is closed)
        } while (!isset($vertices[$vid]));

        // reverse cycle, because cycle is actually built in opposite direction due to checking predecessors
        $vertices = array_reverse($vertices, true);

        // additional edge from last vertex to first vertex
        $vertices[] = reset($vertices);

        return self::factoryCycleFromVertices($vertices, $by, $desc);
    }

    /**
     * create new cycle instance with edges between given vertices
     *
     * @param  Vertex[]|Vertices  $vertices
     * @param  int|null           $by
     * @param  bool               $desc
     * @return Walk
     * @throws UnderflowException if no vertices were given
     * @see Edges::getEdgeOrder() for parameters $by and $desc
     * @uses self::factoryFromVertices()
     */
    public static function factoryCycleFromVertices($vertices, $by = null, $desc = false)
    {
        $cycle = self::factoryFromVertices($vertices, $by, $desc);

        if ($cycle->getEdges()->isEmpty()) {
            throw new InvalidArgumentException('Cycle with no edges can not exist');
        }

        if ($cycle->getVertices()->getVertexFirst() !== $cycle->getVertices()->getVertexLast()) {
            throw new InvalidArgumentException('Cycle has to start and end at the same vertex');
        }

        return $cycle;
    }

    /**
     * create new cycle instance with vertices connected by given edges
     *
     * @param  Edges|Edge[] $edges
     * @param  Vertex       $startVertex
     * @return Walk
     * @throws InvalidArgumentException if the given array of edges does not represent a valid cycle
     * @uses self::factoryFromEdges()
     */
    public static function factoryCycleFromEdges($edges, Vertex $startVertex)
    {
        $cycle = self::factoryFromEdges($edges, $startVertex);

        // ensure this walk is actually a cycle by checking start = end
        if ($cycle->getVertices()->getVertexLast() !== $startVertex) {
            throw new InvalidArgumentException('The given array of edges does not represent a cycle');
        }

        return $cycle;
    }

    /**
     *
     * @var Vertices
     */
    protected $vertices;

    /**
     *
     * @var Edges
     */
    protected $edges;

    protected function __construct($vertices, $edges)
    {
        $this->vertices = Vertices::factory($vertices);
        $this->edges    = Edges::factory($edges);
    }

    /**
     * return original graph
     *
     * @return Graph
     * @uses self::getVertices()
     * @uses Vertices::getVertexFirst()
     * @uses Vertex::getGraph()
     */
    public function getGraph()
    {
        return $this->getVertices()->getVertexFirst()->getGraph();
    }

    /**
     * create new graph clone with only vertices and edges actually in the walk
     *
     * do not add duplicate vertices and edges for loops and intersections, etc.
     *
     * @return Graph
     * @uses Walk::getEdges()
     * @uses Graph::createGraphCloneEdges()
     */
    public function createGraph()
    {
        // create new graph clone with only edges of walk
        $graph = $this->getGraph()->createGraphCloneEdges($this->getEdges());
        $vertices = $this->getVertices()->getMap();
        // get all vertices
        foreach ($graph->getVertices()->getMap() as $vid => $vertex) {
            if (!isset($vertices[$vid])) {
                // remove those not present in the walk (isolated vertices, etc.)
                $vertex->destroy();
            }
        }

        return $graph;
    }

    /**
     * return set of all Edges of walk (in sequence visited in walk, may contain duplicates)
     *
     * If you need to return set a of all unique Edges of walk, use
     * `Walk::getEdges()->getEdgesDistinct()` instead.
     *
     * @return Edges
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * return set of all Vertices of walk (in sequence visited in walk, may contain duplicates)
     *
     * If you need to return set a of all unique Vertices of walk, use
     * `Walk::getVertices()->getVerticesDistinct()` instead.
     *
     * If you need to return the source vertex (first vertex of walk), use
     * `Walk::getVertices()->getVertexFirst()` instead.
     *
     * If you need to return the target/destination vertex (last vertex of walk), use
     * `Walk::getVertices()->getVertexLast()` instead.
     *
     * @return Vertices
     */
    public function getVertices()
    {
        return $this->vertices;
    }

    /**
     * get alternating sequence of vertex, edge, vertex, edge, ..., vertex
     *
     * @return array
     */
    public function getAlternatingSequence()
    {
        $edges    = $this->edges->getVector();
        $vertices = $this->vertices->getVector();

        $ret = array();
        for ($i = 0, $l = count($this->edges); $i < $l; ++$i) {
            $ret []= $vertices[$i];
            $ret []= $edges[$i];
        }
        $ret[] = $vertices[$i];

        return $ret;
    }

    /**
     * check to make sure this walk is still valid (i.e. source graph still contains all vertices and edges)
     *
     * @return bool
     * @uses Walk::getGraph()
     * @uses Graph::getVertices()
     * @uses Graph::getEdges()
     */
    public function isValid()
    {
        $vertices = $this->getGraph()->getVertices()->getMap();
        // check source graph contains all vertices
        foreach ($this->getVertices()->getMap() as $vid => $vertex) {
            // make sure vertex ID exists and has not been replaced
            if (!isset($vertices[$vid]) || $vertices[$vid] !== $vertex) {
                return false;
            }
        }
        $edges = $this->getGraph()->getEdges()->getVector();
        // check source graph contains all edges
        foreach ($this->edges as $edge) {
            if (!in_array($edge, $edges, true)) {
                return false;
            }
        }

        return true;
    }
}

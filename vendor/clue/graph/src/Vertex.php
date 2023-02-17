<?php

namespace Fhaculty\Graph;

use Fhaculty\Graph\Attribute\AttributeAware;
use Fhaculty\Graph\Attribute\AttributeBagReference;
use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Edge\Directed as EdgeDirected;
use Fhaculty\Graph\Edge\Undirected as EdgeUndirected;
use Fhaculty\Graph\Exception\BadMethodCallException;
use Fhaculty\Graph\Exception\InvalidArgumentException;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Set\EdgesAggregate;
use Fhaculty\Graph\Set\Vertices;

class Vertex implements EdgesAggregate, AttributeAware
{
    private $id;

    /**
     * @var Edge[]
     */
    private $edges = array();

    /**
     * @var Graph
     */
    private $graph;

    /**
     * vertex balance
     *
     * @var int|float|NULL
     * @see Vertex::setBalance()
     */
    private $balance;

    /**
     * group number
     *
     * @var int
     * @see Vertex::setGroup()
     */
    private $group = 0;

    private $attributes = array();

    /**
     * Create a new Vertex
     *
     * @param Graph      $graph graph to be added to
     * @param string|int $id    identifier used to uniquely identify this vertex in the graph
     * @see Graph::createVertex() to create new vertices
     */
    public function __construct(Graph $graph, $id)
    {
        if (!is_int($id) && !is_string($id)) {
            throw new InvalidArgumentException('Vertex ID has to be of type integer or string');
        }

        $this->id = $id;
        $this->graph = $graph;

        $graph->addVertex($this);
    }

    /**
     * get graph this vertex is attached to
     *
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function setBalance($balance)
    {
        if ($balance !== NULL && !is_float($balance) && !is_int($balance)) {
            throw new InvalidArgumentException('Invalid balance given - must be numeric');
        }
        $this->balance = $balance;

        return $this;
    }

    /**
     * set group number of this vertex
     *
     * @param  int                      $group
     * @return Vertex                   $this (chainable)
     * @throws InvalidArgumentException if group is not numeric
     */
    public function setGroup($group)
    {
        if (!is_int($group)) {
            throw new InvalidArgumentException('Invalid group number');
        }
        $this->group = $group;

        return $this;
    }

    /**
     * get group number
     *
     * @return int
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * returns id of this Vertex
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * create new directed edge from this start vertex to given target vertex
     *
     * @param  Vertex                   $vertex target vertex
     * @return EdgeDirected
     * @throws InvalidArgumentException
     * @uses Graph::addEdge()
     */
    public function createEdgeTo(Vertex $vertex)
    {
        return new EdgeDirected($this, $vertex);
    }

    /**
     * add new undirected (bidirectional) edge between this vertex and given vertex
     *
     * @param  Vertex                   $vertex
     * @return EdgeUndirected
     * @throws InvalidArgumentException
     * @uses Graph::addEdge()
     */
    public function createEdge(Vertex $vertex)
    {
        return new EdgeUndirected($this, $vertex);
    }

    /**
     * add the given edge to list of connected edges (MUST NOT be called manually)
     *
     * @param  Edge                     $edge
     * @return void
     * @internal
     * @see self::createEdge() instead!
     */
    public function addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
    }

    /**
     * remove the given edge from list of connected edges (MUST NOT be called manually)
     *
     * @param  Edge                     $edge
     * @return void
     * @throws InvalidArgumentException if given edge does not exist
     * @internal
     * @see Edge::destroy() instead!
     */
    public function removeEdge(Edge $edge)
    {
        $id = array_search($edge, $this->edges, true);
        if ($id === false) {
            throw new InvalidArgumentException('Given edge does NOT exist');
        }
        unset($this->edges[$id]);
    }

    /**
     * check whether this vertex has a direct edge to given $vertex
     *
     * @param  Vertex  $vertex
     * @return bool
     * @uses Edge::hasVertexTarget()
     */
    public function hasEdgeTo(Vertex $vertex)
    {
        $that = $this;

        return $this->getEdges()->hasEdgeMatch(function (Edge $edge) use ($that, $vertex) {
            return $edge->isConnection($that, $vertex);
        });
    }

    /**
     * check whether the given vertex has a direct edge to THIS vertex
     *
     * @param  Vertex  $vertex
     * @return bool
     * @uses Vertex::hasEdgeTo()
     */
    public function hasEdgeFrom(Vertex $vertex)
    {
        return $vertex->hasEdgeTo($this);
    }

    /**
     * get set of ALL Edges attached to this vertex
     *
     * @return Edges
     */
    public function getEdges()
    {
        return new Edges($this->edges);
    }

    /**
     * get set of all outgoing Edges attached to this vertex
     *
     * @return Edges
     */
    public function getEdgesOut()
    {
        $that = $this;
        $prev = null;

        return $this->getEdges()->getEdgesMatch(function (Edge $edge) use ($that, &$prev) {
            $ret = $edge->hasVertexStart($that);

            // skip duplicate directed loop edges
            if ($edge === $prev && $edge instanceof EdgeDirected) {
                $ret = false;
            }
            $prev = $edge;

            return $ret;
        });
    }

    /**
     * get set of all ingoing Edges attached to this vertex
     *
     * @return Edges
     */
    public function getEdgesIn()
    {
        $that = $this;
        $prev = null;

        return $this->getEdges()->getEdgesMatch(function (Edge $edge) use ($that, &$prev) {
            $ret = $edge->hasVertexTarget($that);

            // skip duplicate directed loop edges
            if ($edge === $prev && $edge instanceof EdgeDirected) {
                $ret = false;
            }
            $prev = $edge;

            return $ret;
        });
    }

    /**
     * get set of Edges FROM this vertex TO the given vertex
     *
     * @param  Vertex $vertex
     * @return Edges
     * @uses Edge::hasVertexTarget()
     */
    public function getEdgesTo(Vertex $vertex)
    {
        $that = $this;

        return $this->getEdges()->getEdgesMatch(function (Edge $edge) use ($that, $vertex) {
            return $edge->isConnection($that, $vertex);
        });
    }

    /**
     * get set of Edges FROM the given vertex TO this vertex
     *
     * @param  Vertex $vertex
     * @return Edges
     * @uses Vertex::getEdgesTo()
     */
    public function getEdgesFrom(Vertex $vertex)
    {
        return $vertex->getEdgesTo($this);
    }

    /**
     * get set of adjacent Vertices of this vertex (edge FROM or TO this vertex)
     *
     * If there are multiple parallel edges between the same Vertex, it will be
     * returned several times in the resulting Set of Vertices. If you only
     * want unique Vertex instances, use `getVerticesDistinct()`.
     *
     * @return Vertices
     * @uses Edge::hasVertexStart()
     * @uses Edge::getVerticesToFrom()
     * @uses Edge::getVerticesFromTo()
     */
    public function getVerticesEdge()
    {
        $ret = array();
        foreach ($this->edges as $edge) {
            if ($edge->hasVertexStart($this)) {
                $ret []= $edge->getVertexToFrom($this);
            } else {
                $ret []= $edge->getVertexFromTo($this);
            }
        }

        return new Vertices($ret);
    }

    /**
     * get set of all Vertices this vertex has an edge to
     *
     * If there are multiple parallel edges to the same Vertex, it will be
     * returned several times in the resulting Set of Vertices. If you only
     * want unique Vertex instances, use `getVerticesDistinct()`.
     *
     * @return Vertices
     * @uses Vertex::getEdgesOut()
     * @uses Edge::getVerticesToFrom()
     */
    public function getVerticesEdgeTo()
    {
        $ret = array();
        foreach ($this->getEdgesOut() as $edge) {
            $ret []= $edge->getVertexToFrom($this);
        }

        return new Vertices($ret);
    }

    /**
     * get set of all Vertices that have an edge TO this vertex
     *
     * If there are multiple parallel edges from the same Vertex, it will be
     * returned several times in the resulting Set of Vertices. If you only
     * want unique Vertex instances, use `getVerticesDistinct()`.
     *
     * @return Vertices
     * @uses Vertex::getEdgesIn()
     * @uses Edge::getVerticesFromTo()
     */
    public function getVerticesEdgeFrom()
    {
        $ret = array();
        foreach ($this->getEdgesIn() as $edge) {
            $ret []= $edge->getVertexFromTo($this);
        }

        return new Vertices($ret);
    }

    /**
     * destroy vertex and all edges connected to it and remove reference from graph
     *
     * @uses Edge::destroy()
     * @uses Graph::removeVertex()
     */
    public function destroy()
    {
        foreach ($this->getEdges()->getEdgesDistinct() as $edge) {
            $edge->destroy();
        }
        $this->graph->removeVertex($this);
    }

    /**
     * do NOT allow cloning of objects
     *
     * @throws BadMethodCallException
     */
    private function __clone()
    {
        // @codeCoverageIgnoreStart
        throw new BadMethodCallException();
        // @codeCoverageIgnoreEnd
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributeBag()
    {
        return new AttributeBagReference($this->attributes);
    }
}

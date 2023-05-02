<?php

namespace Fhaculty\Graph\Edge;

use Fhaculty\Graph\Attribute\AttributeAware;
use Fhaculty\Graph\Attribute\AttributeBagReference;
use Fhaculty\Graph\Exception\BadMethodCallException;
use Fhaculty\Graph\Exception\InvalidArgumentException;
use Fhaculty\Graph\Exception\LogicException;
use Fhaculty\Graph\Exception\RangeException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Set\VerticesAggregate;
use Fhaculty\Graph\Vertex;

abstract class Base implements VerticesAggregate, AttributeAware
{
    /**
     * weight of this edge
     *
     * @var float|int|NULL
     * @see self::getWeight()
     */
    protected $weight = NULL;

    /**
     * maximum capacity (maximum flow)
     *
     * @var float|int|NULL
     * @see self::getCapacity()
     */
    protected $capacity = NULL;

    /**
     * flow (capacity currently in use)
     *
     * @var float|int|NULL
     * @see self::getFlow()
     */
    protected $flow = NULL;

    protected $attributes = array();

    /**
     * get Vertices that are a target of this edge
     *
     * @return Vertices
     */
    abstract public function getVerticesTarget();

    /**
     * get Vertices that are the start of this edge
     *
     * @return Vertices
     */
    abstract public function getVerticesStart();

    /**
     * return true if this edge is an outgoing edge of the given vertex (i.e. the given vertex is a valid start vertex of this edge)
     *
     * @param  Vertex  $startVertex
     * @return bool
     * @uses Vertex::getVertexToFrom()
     */
    abstract public function hasVertexStart(Vertex $startVertex);

    /**
     * return true if this edge is an ingoing edge of the given vertex (i . e. the given vertex is a valid end vertex of this edge)
     *
     * @param  Vertex  $targetVertex
     * @return bool
     * @uses Vertex::getVertexFromTo()
     */
    abstract function hasVertexTarget(Vertex $targetVertex);

    abstract public function isConnection(Vertex $from, Vertex $to);

    /**
     * returns whether this edge is actually a loop
     *
     * @return bool
     */
    abstract public function isLoop();

    /**
     * get target vertex we can reach with this edge from the given start vertex
     *
     * @param  Vertex                   $startVertex
     * @return Vertex
     * @throws InvalidArgumentException if given $startVertex is not a valid start
     * @see self::hasEdgeFrom() to check if given start is valid
     */
    abstract public function getVertexToFrom(Vertex $startVertex);

    /**
     * get start vertex which can reach us(the given end vertex) with this edge
     *
     * @param  Vertex                   $endVertex
     * @return Vertex
     * @throws InvalidArgumentException if given $startVertex is not a valid end
     * @see self::hasEdgeFrom() to check if given start is valid
     */
    abstract public function getVertexFromTo(Vertex $endVertex);

    /**
     * return weight of edge
     *
     * @return float|int|NULL numeric weight of edge or NULL=not set
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * set new weight for edge
     *
     * @param  float|int|NULL           $weight new numeric weight of edge or NULL=unset weight
     * @return self                     $this (chainable)
     * @throws InvalidArgumentException if given weight is not numeric
     */
    public function setWeight($weight)
    {
        if ($weight !== NULL && !is_float($weight) && !is_int($weight)) {
            throw new InvalidArgumentException('Invalid weight given - must be numeric or NULL');
        }
        $this->weight = $weight;

        return $this;
    }

    /**
     * get total capacity of this edge
     *
     * @return float|int|NULL numeric capacity or NULL=not set
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * get the capacity remaining (total capacity - current flow)
     *
     * @return float|int|NULL numeric capacity remaining or NULL=no upper capacity set
     */
    public function getCapacityRemaining()
    {
        if ($this->capacity === NULL) {
            return NULL;
        }

        return $this->capacity - $this->flow;
    }

    /**
     * set new total capacity of this edge
     *
     * @param  float|int|NULL           $capacity
     * @return self                     $this (chainable)
     * @throws InvalidArgumentException if $capacity is invalid (not numeric or negative)
     * @throws RangeException           if current flow exceeds new capacity
     */
    public function setCapacity($capacity)
    {
        if ($capacity !== NULL) {
            if (!is_float($capacity) && !is_int($capacity)) {
                throw new InvalidArgumentException('Invalid capacity given - must be numeric');
            }
            if ($capacity < 0) {
                throw new InvalidArgumentException('Capacity must not be negative');
            }
            if ($this->flow !== NULL && $this->flow > $capacity) {
                throw new RangeException('Current flow of ' . $this->flow . ' exceeds new capacity');
            }
        }
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * get current flow (capacity currently in use)
     *
     * @return float|int|NULL numeric flow or NULL=not set
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * set new total flow (capacity currently in use)
     *
     * @param  float|int|NULL           $flow
     * @return self                     $this (chainable)
     * @throws InvalidArgumentException if $flow is invalid (not numeric or negative)
     * @throws RangeException           if flow exceeds current maximum capacity
     */
    public function setFlow($flow)
    {
        if ($flow !== NULL) {
            if (!is_float($flow) && !is_int($flow)) {
                throw new InvalidArgumentException('Invalid flow given - must be numeric');
            }
            if ($flow < 0) {
                throw new InvalidArgumentException('Flow must not be negative');
            }
            if ($this->capacity !== NULL && $flow > $this->capacity) {
                throw new RangeException('New flow exceeds maximum capacity');
            }
        }
        $this->flow = $flow;

        return $this;
    }

    /**
     * get set of all Vertices this edge connects
     *
     * @return Vertices
     */
    //abstract public function getVertices();

    /**
     * get graph instance this edge is attached to
     *
     * @return Graph
     * @throws LogicException
     */
    public function getGraph()
    {
        foreach ($this->getVertices() as $vertex) {
            return $vertex->getGraph();

            // the following code can only be reached if this edge does not
            // contain any vertices (invalid state), so ignore its coverage
            // @codeCoverageIgnoreStart
        }

        throw new LogicException('Internal error: should not be reached');
        // @codeCoverageIgnoreEnd
    }

    /**
     * destroy edge and remove reference from vertices and graph
     *
     * @uses Graph::removeEdge()
     * @uses Vertex::removeEdge()
     * @return void
     */
    public function destroy()
    {
        $this->getGraph()->removeEdge($this);
        foreach ($this->getVertices() as $vertex) {
            $vertex->removeEdge($this);
        }
    }

    /**
     * create new clone of this edge between adjacent vertices
     *
     * @return self new edge
     * @uses Graph::createEdgeClone()
     */
    public function createEdgeClone()
    {
        return $this->getGraph()->createEdgeClone($this);
    }

    /**
     * create new clone of this edge inverted (in opposite direction) between adjacent vertices
     *
     * @return self new edge
     * @uses Graph::createEdgeCloneInverted()
     */
    public function createEdgeCloneInverted()
    {
        return $this->getGraph()->createEdgeCloneInverted($this);
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

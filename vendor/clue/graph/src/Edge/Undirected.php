<?php

namespace Fhaculty\Graph\Edge;

use Fhaculty\Graph\Exception\InvalidArgumentException;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Set\Vertices;

class Undirected extends Base
{
    /**
     * vertex a
     *
     * @var Vertex
     */
    private $a;

    /**
     * vertex b
     *
     * @var Vertex
     */
    private $b;

    /**
     * create a new undirected edge between given vertices
     *
     * @param Vertex $a
     * @param Vertex $b
     * @see Vertex::createEdge() instead
     */
    public function __construct(Vertex $a, Vertex $b)
    {
        if ($a->getGraph() !== $b->getGraph()) {
            throw new InvalidArgumentException('Vertices have to be within the same graph');
        }

        $this->a = $a;
        $this->b = $b;

        $a->getGraph()->addEdge($this);
        $a->addEdge($this);
        $b->addEdge($this);
    }

    public function getVerticesTarget()
    {
        return new Vertices(array($this->b, $this->a));
    }

    public function getVerticesStart()
    {
        return new Vertices(array($this->a, $this->b));
    }

    public function getVertices()
    {
        return new Vertices(array($this->a, $this->b));
    }

    public function isConnection(Vertex $from, Vertex $to)
    {
        // one way                or                        other way
        return (($this->a === $from && $this->b === $to) || ($this->b === $from && $this->a === $to));
    }

    public function isLoop()
    {
        return ($this->a === $this->b);
    }

    public function getVertexToFrom(Vertex $startVertex)
    {
        if ($this->a === $startVertex) {
            return $this->b;
        } elseif ($this->b === $startVertex) {
            return $this->a;
        } else {
            throw new InvalidArgumentException('Invalid start vertex');
        }
    }

    public function getVertexFromTo(Vertex $endVertex)
    {
        if ($this->a === $endVertex) {
            return $this->b;
        } elseif ($this->b === $endVertex) {
            return $this->a;
        } else {
            throw new InvalidArgumentException('Invalid end vertex');
        }
    }

    public function hasVertexStart(Vertex $startVertex)
    {
        return ($this->a === $startVertex || $this->b === $startVertex);
    }

    public function hasVertexTarget(Vertex $targetVertex)
    {
        // same implementation as direction does not matter
        return $this->hasVertexStart($targetVertex);
    }
}

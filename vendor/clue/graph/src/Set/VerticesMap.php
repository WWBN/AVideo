<?php

namespace Fhaculty\Graph\Set;

use Fhaculty\Graph\Exception\OutOfBoundsException;
use Fhaculty\Graph\Vertex;

/**
 * A set of Vertices that are already stored in a vertex ID => Vertex instance mapping array
 *
 * Among others, using a mapped array significantly speeds up accessing vertices
 * by ID. However, there's no way to store multiple vertices with the same ID
 * (i.e. each Vertex ID has to be unique).
 */
class VerticesMap extends Vertices
{
    public function getMap()
    {
        return $this->vertices;
    }

    public function getVertexId($id)
    {
        if (!isset($this->vertices[$id])) {
            throw new OutOfBoundsException('Invalid vertex ID');
        }
        return $this->vertices[$id];
    }

    public function hasVertexId($id)
    {
        return isset($this->vertices[$id]);
    }

    public function getVerticesDistinct()
    {
        return $this;
    }

    public function getIds()
    {
        return array_keys($this->vertices);
    }

    public function getIndexVertex(Vertex $vertex)
    {
        $id = $vertex->getId();
        if (!isset($this->vertices[$id]) || $this->vertices[$id] !== $vertex) {
            throw new OutOfBoundsException();
        }
        return $id;
    }

    /**
     *
     * @return VerticesMap
     */
    public function getVertices()
    {
        return $this;
    }

    public function hasDuplicates()
    {
        return false;
    }
}

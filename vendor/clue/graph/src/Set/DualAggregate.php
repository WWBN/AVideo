<?php

namespace Fhaculty\Graph\Set;

/**
 * A DualAggregate provides access to both its Vertices and its Edges
 *
 * This is the simple base interface for any Graph-like structure / data type
 * which contains a Set of Edges and a Set of Vertices, such as the Graph class
 * itself and the Walk class.
 */
interface DualAggregate extends VerticesAggregate, EdgesAggregate
{
    /**
     * returns a set of ALL Edges in this graph
     *
     * @return Edges
     */
    // abstract public function getEdges();

    /**
     * returns a set of all Vertices
     *
     * @return Vertices
     */
    // abstract public function getVertices();
}

<?php

namespace Fhaculty\Graph\Set;

use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Exception\InvalidArgumentException;
use Fhaculty\Graph\Exception\OutOfBoundsException;
use Fhaculty\Graph\Exception\UnderflowException;

/**
 * A Set of Vertices
 *
 * Contains any number of Vertex instances.
 *
 * The Set is a readonly instance and it provides methods to get single Vertex
 * instances or to get a new Set of Vertices. This way it's safe to pass around
 * the original Set of Vertices, because it will never be modified.
 */
class Vertices implements \Countable, \IteratorAggregate, VerticesAggregate
{
    /**
     * order by vertex ID
     *
     * @var int
     * @see Vertex::getId()
     */
    const ORDER_ID = 1;

    /**
     * random/shuffled order
     *
     * @var int
     */
    const ORDER_RANDOM = 5;

    /**
     * order by vertex group
     *
     * @var int
     * @see Vertex::getGroup()
     */
    const ORDER_GROUP = 6;

    protected $vertices = array();

    /**
     * create new Vertices instance
     *
     * You can pass in just about anything that can be expressed as a Set of
     * Vertices, such as:
     * - an array of Vertex instances
     * - any Algorithm that implements the VerticesAggregate interface
     * - a Graph instance or
     * - an existing Set of Vertices which will be returned as-is
     *
     * @param array|Vertices|VerticesAggregate $vertices
     * @return Vertices
     */
    public static function factory($vertices)
    {
        if ($vertices instanceof VerticesAggregate) {
            return $vertices->getVertices();
        }
        return new self($vertices);
    }

    /**
     * create new Vertices instance that references the given source array of Vertex instances
     *
     * Any changes in the referenced source array will automatically be
     * reflected in this Set of Vertices, e.g. if you add a Vertex instance to
     * the array, it will automatically be included in this Set.
     *
     * @param array $verticesArray
     * @return Vertices
     */
    public static function factoryArrayReference(array &$verticesArray)
    {
        $vertices = new static();
        $vertices->vertices =& $verticesArray;
        return $vertices;
    }

    /**
     * instantiate new Set of Vertices
     *
     * @param array $vertices
     */
    public function __construct(array $vertices = array())
    {
        $this->vertices = $vertices;
    }

    /**
     * get Vertex with the given vertex $id
     *
     * @param int|string $id
     * @return Vertex
     * @throws OutOfBoundsException if no Vertex with the given ID exists
     * @uses self::getVertexMatch()
     */
    public function getVertexId($id)
    {
        try {
            return $this->getVertexMatch($this->getCallbackId($id));
        }
        catch (UnderflowException $e) {
            throw new OutOfBoundsException('Vertex ' . $id . ' does not exist', 0, $e);
        }
    }

    /**
     * checks whether given vertex ID exists in this set of vertices
     *
     * @param int|string $id identifier of Vertex
     * @return bool
     * @uses self::hasVertexMatch()
     */
    public function hasVertexId($id)
    {
        return $this->hasVertexMatch($this->getCallbackId($id));
    }

    /**
     * get array index for given Vertex
     *
     * not every set of Vertices represents a map, as such array index and
     * Vertex ID do not necessarily have to match.
     *
     * @param Vertex $vertex
     * @throws OutOfBoundsException
     * @return mixed
     */
    public function getIndexVertex(Vertex $vertex)
    {
        $id = array_search($vertex, $this->vertices, true);
        if ($id === false) {
            throw new OutOfBoundsException('Given vertex does NOT exist');
        }
        return $id;
    }

    /**
     * return first Vertex in this set of Vertices
     *
     * some algorithms do not need a particular vertex, but merely a (random)
     * starting point. this is a convenience function to just pick the first
     * vertex from the list of known vertices.
     *
     * @return Vertex             first Vertex in this set of Vertices
     * @throws UnderflowException if set is empty
     * @see self::getVertexOrder() if you need to apply ordering first
     */
    public function getVertexFirst()
    {
        if (!$this->vertices) {
            throw new UnderflowException('Does not contain any vertices');
        }
        reset($this->vertices);

        return current($this->vertices);
    }

    /**
     * return last Vertex in this set of Vertices
     *
     * @return Vertex             last Vertex in this set of Vertices
     * @throws UnderflowException if set is empty
     */
    public function getVertexLast()
    {
        if (!$this->vertices) {
            throw new UnderflowException('Does not contain any vertices');
        }
        end($this->vertices);

        return current($this->vertices);
    }

    /**
     * return first Vertex that matches the given callback filter function
     *
     * @param callable $callbackCheck
     * @return Vertex
     * @throws UnderflowException if no Vertex matches the given callback filter function
     * @uses self::getVertexMatchOrNull()
     * @see self::getVerticesMatch() if you want to return *all* Vertices that match
     */
    public function getVertexMatch($callbackCheck)
    {
        $ret = $this->getVertexMatchOrNull($callbackCheck);
        if ($ret === null) {
            throw new UnderflowException('No vertex found');
        }
        return $ret;
    }

    /**
     * checks whether there's a Vertex that matches the given callback filter function
     *
     * @param callable $callbackCheck
     * @return bool
     * @see self::getVertexMatch() to return the Vertex instance that matches the given callback filter function
     * @uses self::getVertexMatchOrNull()
     */
    public function hasVertexMatch($callbackCheck)
    {
        return ($this->getVertexMatchOrNull($callbackCheck) !== null);
    }

    /**
     * get a new set of Vertices that match the given callback filter function
     *
     * This only keeps Vertex elements if the $callbackCheck returns a bool
     * true and filters out everything else.
     *
     * Vertex index positions will be left unchanged, so if you call this method
     * on a VerticesMap, it will also return a VerticesMap.
     *
     * @param callable $callbackCheck
     * @return Vertices a new Vertices instance
     * @see self::getVertexMatch()
     */
    public function getVerticesMatch($callbackCheck)
    {
        return new static(array_filter($this->vertices, $callbackCheck));
    }

    /**
     * get new Set of Vertices ordered by given criterium $orderBy
     *
     * Vertex index positions will be left unchanged, so if you call this method
     * on a VerticesMap, it will also return a VerticesMap.
     *
     * @param  int                      $orderBy  criterium to sort by. see Vertex::ORDER_ID, etc.
     * @param  bool                     $desc     whether to return biggest first (true) instead of smallest first (default:false)
     * @return Vertices                 a new Vertices set ordered by the given $orderBy criterium
     * @throws InvalidArgumentException if criterium is unknown
     * @see self::getVertexOrder()
     */
    public function getVerticesOrder($orderBy, $desc = false)
    {
        if ($orderBy === self::ORDER_RANDOM) {
            // shuffle the vertex positions
            $keys = array_keys($this->vertices);
            shuffle($keys);

            // re-order according to shuffled vertex positions
            $vertices = array();
            foreach ($keys as $key) {
                $vertices[$key] = $this->vertices[$key];
            }

            // create iterator for shuffled array (no need to check DESC flag)
            return new static($vertices);
        }

        $callback = $this->getCallback($orderBy);
        $array    = $this->vertices;

        uasort($array, function (Vertex $va, Vertex $vb) use ($callback, $desc) {
            $ra = $callback($desc ? $vb : $va);
            $rb = $callback($desc ? $va : $vb);

            if ($ra < $rb) {
                return -1;
            } elseif ($ra > $rb) {
                return 1;
            } else {
                return 0;
            }
        });

        return new static($array);
    }

    /**
     * get intersection of Vertices with given other Vertices
     *
     * The intersection contains all Vertex instances that are present in BOTH
     * this set of Vertices and the given set of other Vertices.
     *
     * Vertex index/keys will be preserved from original array.
     *
     * Duplicate Vertex instances will be kept if the corresponding number of
     * Vertex instances is also found in $otherVertices.
     *
     * @param Vertices|Vertex[] $otherVertices
     * @return Vertices a new Vertices set
     */
    public function getVerticesIntersection($otherVertices)
    {
        $otherArray = self::factory($otherVertices)->getVector();

        $vertices = array();
        foreach ($this->vertices as $vid => $vertex) {
            $i = array_search($vertex, $otherArray, true);

            if ($i !== false) {
                // remove from other array in order to check for duplicate matches
                unset($otherArray[$i]);

                $vertices[$vid] = $vertex;
            }
        }

        return new static($vertices);
    }

    /**
     * get first vertex (optionally ordered by given criterium $by) from given array of vertices
     *
     * @param  int                      $orderBy  criterium to sort by. see Vertex::ORDER_ID, etc.
     * @param  bool                     $desc     whether to return biggest (true) instead of smallest (default:false)
     * @return Vertex
     * @throws InvalidArgumentException if criterium is unknown
     * @throws UnderflowException       if no vertices exist
     * @see self::getVerticesOrder()
     */
    public function getVertexOrder($orderBy, $desc=false)
    {
        if (!$this->vertices) {
            throw new UnderflowException('No vertex found');
        }
        // random order
        if ($orderBy === self::ORDER_RANDOM) {
            // just return by random key (no need to check for DESC flag)
            return $this->vertices[array_rand($this->vertices)];
        }

        $callback = $this->getCallback($orderBy);

        $ret = NULL;
        $best = NULL;
        foreach ($this->vertices as $vertex) {
            $now = $callback($vertex);

            if ($ret === NULL || ($desc && $now > $best) || (!$desc && $now < $best)) {
                $ret = $vertex;
                $best = $now;
            }
        }

        return $ret;
    }

    /**
     * return self reference to Set of Vertices
     *
     * @return Vertices
     * @see self::factory()
     */
    public function getVertices()
    {
        return $this;
    }

    /**
     * get a new set of Vertices where each Vertex is distinct/unique
     *
     * @return VerticesMap a new VerticesMap instance
     * @uses self::getMap()
     */
    public function getVerticesDistinct()
    {
        return new VerticesMap($this->getMap());
    }

    /**
     * get a mapping array of Vertex ID => Vertex instance and thus remove duplicate vertices
     *
     * @return Vertex[] Vertex ID => Vertex instance
     * @uses Vertex::getId()
     */
    public function getMap()
    {
        $vertices = array();
        foreach ($this->vertices as $vertex) {
            $vertices[$vertex->getId()] = $vertex;
        }
        return $vertices;
    }

    /**
     * return array of Vertex IDs
     *
     * @return array
     */
    public function getIds()
    {
        $ids = array();
        foreach ($this->vertices as $vertex) {
            $ids []= $vertex->getId();
        }
        return $ids;
    }

    /**
     * return array of Vertex instances
     *
     * @return Vertex[]
     */
    public function getVector()
    {
        return array_values($this->vertices);
    }

    /**
     * count number of vertices
     *
     * @return int
     * @see self::isEmpty()
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->vertices);
    }

    /**
     * check whether this Set of Vertices is empty
     *
     * A Set if empty if no single Vertex instance is added. This is faster
     * than calling `count() === 0`.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->vertices;
    }

    /**
     * check whether this set contains any duplicate vertex instances
     *
     * @return bool
     * @uses self::getMap()
     */
    public function hasDuplicates()
    {
        return (count($this->vertices) !== count($this->getMap()));
    }

    /**
     * get Iterator
     *
     * This method implements the IteratorAggregate interface and allows this
     * Set of Vertices to be used in foreach loops.
     *
     * @return \IteratorIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \IteratorIterator(new \ArrayIterator($this->vertices));
    }

    /**
     * call given $callback on each Vertex and sum their results
     *
     * @param callable $callback
     * @return number
     * @throws InvalidArgumentException for invalid callbacks
     * @uses self::getCallback()
     */
    public function getSumCallback($callback)
    {
        $callback = $this->getCallback($callback);

        // return array_sum(array_map($callback, $this->vertices));

        $sum = 0;
        foreach ($this->vertices as $vertex) {
            $sum += $callback($vertex);
        }
        return $sum;
    }

    private function getCallbackId($id)
    {
        return function (Vertex $vertex) use ($id) {
            return ($vertex->getId() == $id);
        };
    }

    private function getVertexMatchOrNull($callbackCheck)
    {
        $callbackCheck = $this->getCallback($callbackCheck);

        foreach ($this->vertices as $vertex) {
            if ($callbackCheck($vertex)) {
                return $vertex;
            }
        }
        return null;
    }

    /**
     * get callback/Closure to be called on Vertex instances for given callback identifier
     *
     * @param callable|int $callback
     * @throws InvalidArgumentException
     * @return callable
     */
    private function getCallback($callback)
    {
        if (is_callable($callback)) {
            if (is_array($callback)) {
                $callback = function (Vertex $vertex) use ($callback) {
                    return call_user_func($callback, $vertex);
                };
            }
            return $callback;
        }

        static $methods = array(
            self::ORDER_ID => 'getId',
            self::ORDER_GROUP => 'getGroup'
        );

        if (!is_int($callback) || !isset($methods[$callback])) {
            throw new InvalidArgumentException('Invalid callback given');
        }

        $method = $methods[$callback];

        return function (Vertex $vertex) use ($method) {
            return $vertex->$method();
        };
    }
}

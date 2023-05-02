<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\GraphViz;

/**
 * Class representing a graph; this may be a main graph but also a subgraph.
 *
 * In case of a subgraph:
 * When the name of the subgraph is prefixed with _cluster_ then the contents
 * of this graph will be grouped and a border will be added. Otherwise it is
 * used as logical container to place defaults in.
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 *
 * @method Graph setRankSep(string $rankSep)
 * @method Graph setCenter(string $center)
 * @method Graph setRank(string $rank)
 * @method Graph setRankDir(string $rankDir)
 * @method Graph setSplines(string $splines)
 * @method Graph setConcentrate(string $concentrate)
 */
class Graph
{

    /** @var string Name of this graph */
    protected $name = 'G';

    /** @var string Type of this graph; may be digraph, graph or subgraph */
    protected $type = 'digraph';

    /** @var bool If the graph is strict then multiple edges are not allowed between the same pairs of nodes */
    protected $strict = false;

    /** @var \phpDocumentor\GraphViz\Attribute[] A list of attributes for this Graph */
    protected $attributes = array();

    /** @var \phpDocumentor\GraphViz\Graph[] A list of subgraphs for this Graph */
    protected $graphs = array();

    /** @var \phpDocumentor\GraphViz\Node[] A list of nodes for this Graph */
    protected $nodes = array();

    /** @var \phpDocumentor\GraphViz\Edge[] A list of edges / arrows for this Graph */
    protected $edges = array();

    /** @var string The path to execute dot from */
    protected $path = '';

    /**
     * Factory method to instantiate a Graph so that you can use fluent coding
     * to chain everything.
     *
     * @param string $name        The name for this graph.
     * @param bool   $directional Whether this is a directed or undirected graph.
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public static function create($name = 'G', $directional = true)
    {
        $graph = new self();
        $graph
            ->setName($name)
            ->setType($directional ? 'digraph' : 'graph');

        return $graph;
    }

    /**
     * Sets the path for the execution. Only needed if it is not in the PATH env.
     *
     * @param string $path The path to execute dot from
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function setPath($path)
    {
        if ($path && $path = realpath($path)) {
            $this->path = $path . DIRECTORY_SEPARATOR;
        }
        return $this;
    }

    /**
     * Sets the name for this graph.
     *
     * If this is a subgraph you can prefix the name with _cluster_ to group all
     * contained nodes and add a border.
     *
     * @param string $name The new name for this graph.
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name for this Graph.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the type for this graph.
     *
     * @param string $type Must be either "digraph", "graph" or "subgraph".
     *
     * @throws \InvalidArgumentException if $type is not "digraph", "graph" or
     *  "subgraph".
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function setType($type)
    {
        if (!in_array($type, array('digraph', 'graph', 'subgraph'))) {
            throw new \InvalidArgumentException(
                'The type for a graph must be either "digraph", "graph" or '
                . '"subgraph"'
            );
        }

        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of this Graph.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set if the Graph should be strict. If the graph is strict then
     * multiple edges are not allowed between the same pairs of nodes
     *
     * @param bool $isStrict
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function setStrict($isStrict)
    {
        $this->strict = $isStrict;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Magic method to provide a getter/setter to add attributes on the Graph.
     *
     * Using this method we make sure that we support any attribute without
     * too much hassle. If the name for this method does not start with get
     * or set we return null.
     *
     * Set methods return this graph (fluent interface) whilst get methods
     * return the attribute value.
     *
     * @param string  $name      Name of the method including get/set
     * @param mixed[] $arguments The arguments, should be 1: the value
     *
     * @return \phpDocumentor\GraphViz\Attribute[]|\phpDocumentor\GraphViz\Graph|null
     */
    function __call($name, $arguments)
    {
        $key = strtolower(substr($name, 3));
        if (strtolower(substr($name, 0, 3)) === 'set') {
            $this->attributes[$key] = new Attribute($key, $arguments[0]);

            return $this;
        }
        if (strtolower(substr($name, 0, 3)) === 'get') {
            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * Adds a subgraph to this graph; automatically changes the type to subgraph.
     *
     * Please note that an index is maintained using the name of the subgraph.
     * Thus if you have 2 subgraphs with the same name that the first will be
     * overwritten by the latter.
     *
     * @param \phpDocumentor\GraphViz\Graph $graph The graph to add onto this graph as
     *  subgraph.
     *
     * @see \phpDocumentor\GraphViz\Graph::create()
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function addGraph(\phpDocumentor\GraphViz\Graph $graph)
    {
        $graph->setType('subgraph');
        $this->graphs[$graph->getName()] = $graph;
        return $this;
    }

    /**
     * Checks whether a graph with a certain name already exists.
     *
     * @param string $name Name of the graph to find.
     *
     * @return bool
     */
    public function hasGraph($name)
    {
        return isset($this->graphs[$name]);
    }

    /**
     * Returns the subgraph with a given name.
     *
     * @param string $name Name of the requested graph.
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function getGraph($name)
    {
        return $this->graphs[$name];
    }

    /**
     * Sets a node in the $nodes array; uses the name of the node as index.
     *
     * Nodes can be retrieved by retrieving the property with the same name.
     * Thus 'node1' can be retrieved by invoking: $graph->node1
     *
     * @param \phpDocumentor\GraphViz\Node $node The node to set onto this Graph.
     *
     * @see \phpDocumentor\GraphViz\Node::create()
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function setNode(Node $node)
    {
        $this->nodes[$node->getName()] = $node;
        return $this;
    }

    /**
     * Finds a node in this graph or any of its subgraphs.
     *
     * @param string $name Name of the node to find.
     *
     * @return \phpDocumentor\GraphViz\Node
     */
    public function findNode($name)
    {
        if (isset($this->nodes[$name])) {
            return $this->nodes[$name];
        }

        foreach ($this->graphs as $graph) {
            $node = $graph->findNode($name);
            if ($node) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Sets a node using a custom name.
     *
     * @param string                       $name  Name of the node.
     * @param \phpDocumentor\GraphViz\Node $value Node to set on the given name.
     *
     * @see \phpDocumentor\GraphViz\Graph::setNode()
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    function __set($name, $value)
    {
        $this->nodes[$name] = $value;
        return $this;
    }


    /**
     * Returns the requested node by its name.
     *
     * @param string $name The name of the node to retrieve.
     *
     * @see \phpDocumentor\GraphViz\Graph::setNode()
     *
     * @return \phpDocumentor\GraphViz\Node
     */
    function __get($name)
    {
        return isset($this->nodes[$name]) ? $this->nodes[$name] : null;
    }

    /**
     * Links two nodes to eachother and registers the Edge onto this graph.
     *
     * @param \phpDocumentor\GraphViz\Edge $edge The link between two classes.
     *
     * @see \phpDocumentor\GraphViz\Edge::create()
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function link(Edge $edge)
    {
        $this->edges[] = $edge;
        return $this;
    }

    /**
     * Exports this graph to a generated image.
     *
     * This is the only method that actually requires GraphViz.
     *
     * @param string $type     The type to export to; see the link above for a
     *  list of supported types.
     * @param string $filename The path to write to.
     *
     * @uses GraphViz/dot
     *
     * @link http://www.graphviz.org/content/output-formats
     *
     * @throws \phpDocumentor\GraphViz\Exception if an error occurred in GraphViz.
     *
     * @return \phpDocumentor\GraphViz\Graph
     */
    public function export($type, $filename)
    {
        $type = escapeshellarg($type);
        $filename = escapeshellarg($filename);

        // write the dot file to a temporary file
        $tmpfile = tempnam(sys_get_temp_dir(), 'gvz');
        file_put_contents($tmpfile, (string)$this);

        // escape the temp file for use as argument
        $tmpfileArg = escapeshellarg($tmpfile);

        // create the dot output
        $output = array();
        $code = 0;
        exec($this->path . "dot -T$type -o$filename < $tmpfileArg 2>&1", $output, $code);
        unlink($tmpfile);

        if ($code != 0) {
            throw new Exception(
                'An error occurred while creating the graph; GraphViz returned: '
                . implode(PHP_EOL, $output)
            );
        }

        return $this;
    }

    /**
     * Generates a DOT file for use with GraphViz.
     *
     * GraphViz is not used in this method; it is safe to call it even without
     * GraphViz installed.
     *
     * @return string
     */
    public function __toString()
    {
        $elements = array_merge(
            $this->graphs, $this->attributes, $this->edges, $this->nodes
        );

        $attributes = array();
        foreach ($elements as $value) {
            $attributes[] = (string)$value;
        }
        $attributes = implode(PHP_EOL, $attributes);

        $strict = ($this->isStrict() ? 'strict ' : '');

        return <<<DOT
{$strict}{$this->getType()} "{$this->getName()}" {
$attributes
}
DOT;
    }

}

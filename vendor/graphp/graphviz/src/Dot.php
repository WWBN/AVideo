<?php

namespace Graphp\GraphViz;

use Graphp\GraphViz\GraphViz;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Exporter\ExporterInterface;

class Dot implements ExporterInterface
{
    private $graphviz;

    public function __construct(GraphViz $graphviz = null)
    {
        if ($graphviz === null) {
            $graphviz = new GraphViz();
        }

        $this->graphviz = $graphviz;
    }

    public function getOutput(Graph $graph)
    {
        return $this->graphviz->createScript($graph);
    }
}

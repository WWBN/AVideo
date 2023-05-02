<?php

use Graphp\GraphViz\GraphViz;

require __DIR__ . '/../vendor/autoload.php';

$graph = new Fhaculty\Graph\Graph();

$a = $graph->createVertex('Entity');
$a->setAttribute('graphviz.shape', 'record');
$a->setAttribute('graphviz.label', GraphViz::raw('"{\N||+ touch()}"'));

$b = $graph->createVertex('Block');
$b->createEdgeTo($a);
$b->setAttribute('graphviz.shape', 'record');
$b->setAttribute('graphviz.label', GraphViz::raw('"{\N|- size:int|+ touch()}"'));

$graphviz = new GraphViz();
echo $graphviz->createScript($graph);
$graphviz->display($graph);

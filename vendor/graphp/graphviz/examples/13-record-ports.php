<?php

use Graphp\GraphViz\GraphViz;

require __DIR__ . '/../vendor/autoload.php';

$graph = new Fhaculty\Graph\Graph();

$a = $graph->createVertex();
$a->setAttribute('graphviz.shape', 'Mrecord');
$a->setAttribute('graphviz.label', GraphViz::raw('"<f0> left |<middle> middle |<f2> right"'));

$b = $graph->createVertex();
$b->setAttribute('graphviz.shape', 'Mrecord');
$b->setAttribute('graphviz.label', GraphViz::raw('"<f0> left |<f1> middle |<right> right"'));

// a:middle -> b:right
$edge = $a->createEdgeTo($b);
$edge->setAttribute('graphviz.tailport', 'middle');
$edge->setAttribute('graphviz.headport', 'right');

$graphviz = new GraphViz();
echo $graphviz->createScript($graph);
$graphviz->display($graph);

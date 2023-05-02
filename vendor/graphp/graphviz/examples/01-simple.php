<?php

require __DIR__ . '/../vendor/autoload.php';

$graph = new Fhaculty\Graph\Graph();

$blue = $graph->createVertex('blue');
$blue->setAttribute('graphviz.color', 'blue');

$red = $graph->createVertex('red');
$red->setAttribute('graphviz.color', 'red');

$edge = $blue->createEdgeTo($red);
$edge->setAttribute('graphviz.color', 'grey');

$graphviz = new Graphp\GraphViz\GraphViz();
$graphviz->display($graph);

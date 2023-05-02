<?php

use Graphp\GraphViz\GraphViz;

require __DIR__ . '/../vendor/autoload.php';

$graph = new Fhaculty\Graph\Graph();

$a = $graph->createVertex('Entity');
$a->setAttribute('graphviz.shape', 'none');
$a->setAttribute('graphviz.label', GraphViz::raw('<
<table cellspacing="0" border="0" cellborder="1">
    <tr><td bgcolor="#eeeeee"><b>\N</b></td></tr>
    <tr><td></td></tr><tr>
    <td>+ touch()</td></tr>
</table>>'));

$b = $graph->createVertex('Block');
$b->createEdgeTo($a);
$b->setAttribute('graphviz.shape', 'none');
$b->setAttribute('graphviz.label', GraphViz::raw('<
<table cellspacing="0" border="0" cellborder="1">
    <tr><td bgcolor="#eeeeee"><b>\N</b></td></tr>
    <tr><td>- size:int</td></tr>
    <tr><td>+ touch()</td></tr>
</table>>'));

$graphviz = new GraphViz();
echo $graphviz->createScript($graph);
$graphviz->display($graph);

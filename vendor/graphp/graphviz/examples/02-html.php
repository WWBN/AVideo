<?php

// $ php -S localhost:8080 examples/02-html.php

require __DIR__ . '/../vendor/autoload.php';

$graph = new Fhaculty\Graph\Graph();
$graph->setAttribute('graphviz.graph.rankdir', 'LR');

$hello = $graph->createVertex('hello');
$world = $graph->createVertex('wörld');
$hello->createEdgeTo($world);

$graphviz = new Graphp\GraphViz\GraphViz();
$graphviz->setFormat('svg');

echo '<!DOCTYPE html>
<html>
<head>
<title>hello wörld</title>
<body>
' . $graphviz->createImageHtml($graph) . '
</body>
</html>
';

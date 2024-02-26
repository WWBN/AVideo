<?php

namespace Fhaculty\Graph\Exporter;

use Fhaculty\Graph\Graph;

interface ExporterInterface
{
    public function getOutput(Graph $graph);
}

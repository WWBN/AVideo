# graphp/graph

[![CI status](https://github.com/graphp/graph/actions/workflows/ci.yml/badge.svg?branch=v0.9.x)](https://github.com/graphp/graph/actions)

GraPHP is the mathematical graph/network library written in PHP.

>   You're viewing the contents of the `v0.9.x` release branch, note that active
    development continues on another branch, see `master` branch for more details.

**Table of contents**

* [Quickstart examples](#quickstart-examples)
* [Features](#features)
* [Components](#components)
    * [Graph drawing](#graph-drawing)
    * [Common algorithms](#common-algorithms)
* [Install](#install)
* [Tests](#tests)
* [Contributing](#contributing)
* [License](#license)

## Quickstart examples

Once [installed](#install), let's initialize a sample graph:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$graph = new Fhaculty\Graph\Graph();

// create some cities
$rome = $graph->createVertex('Rome');
$madrid = $graph->createVertex('Madrid');
$cologne = $graph->createVertex('Cologne');

// build some roads
$cologne->createEdgeTo($madrid);
$madrid->createEdgeTo($rome);
// create loop
$rome->createEdgeTo($rome);
```

Let's see which city (Vertex) has a road (i.e. an edge pointing) to Rome:

```php
foreach ($rome->getVerticesEdgeFrom() as $vertex) {
    echo $vertex->getId().' leads to rome'.PHP_EOL;
    // result: Madrid and Rome itself
}
```

## Features

This library is built around the concept of [mathematical graph theory](https://en.wikipedia.org/wiki/Graph_theory) (i.e. it is **not** a [charting](https://en.wikipedia.org/wiki/Chart) library for drawing a [graph of a function](https://en.wikipedia.org/wiki/Graph_of_a_function)). In essence, a graph is a set of *nodes* with any number of *connections* in between. In graph theory, [vertices](https://en.wikipedia.org/wiki/Vertex_%28graph_theory%29) (plural of vertex) are an abstract representation of these *nodes*, while *connections* are represented as *edges*. Edges may be either undirected ("two-way") or directed ("one-way", aka di-edges, arcs).

Depending on how the edges are constructed, the whole graph can either be undirected, can be a [directed graph](https://en.wikipedia.org/wiki/Directed_graph) (aka digraph) or be a [mixed graph](https://en.wikipedia.org/wiki/Mixed_graph). Edges are also allowed to form [loops](https://en.wikipedia.org/wiki/Loop_%28graph_theory%29) (i.e. an edge from vertex A pointing to vertex A again). Also, [multiple edges](https://en.wikipedia.org/wiki/Multiple_edges) from vertex A to vertex B  are supported as well (aka parallel edges), effectively forming a [multigraph](https://en.wikipedia.org/wiki/Multigraph) (aka pseudograph). And of course, any combination thereof is supported as well. While many authors try to differentiate between these core concepts, this library tries hard to not impose any artificial limitations or assumptions on your graphs.

## Components

This library provides the core data structures for working with graphs, its vertices, edges and attributes.

There are several official components built on top of these structures to provide commonly needed functionality.
This architecture allows these components to be used independently and on demand only.

Following is a list of some highlighted components. A list of all official components can be found in the [graphp project](https://github.com/graphp).

### Graph drawing

This library is built to support visualizing graph images, including them into webpages, opening up images from within CLI applications and exporting them as PNG, JPEG or SVG file formats (among many others). Because [graph drawing](https://en.wikipedia.org/wiki/Graph_drawing) is a complex area on its own, the actual layouting of the graph is left up to the excellent [GraphViz](https://www.graphviz.org/) "Graph Visualization Software" and we merely provide some convenient APIs to interface with GraphViz.

See [graphp/graphviz](https://github.com/graphp/graphviz) for more details.

### Common algorithms

Besides graph drawing, one of the most common things to do with graphs is running algorithms to solve common graph problems.
Therefore this library is being used as the basis for implementations for a number of commonly used graph algorithms:

* Search
    * Deep first (DFS)
    * Breadth first search (BFS)
* Shortest path
    * [Dijkstra](https://en.wikipedia.org/wiki/Dijkstra%27s_algorithm)
    * Moore-Bellman-Ford (MBF)
    * Counting number of hops (simple BFS)
* [Minimum spanning tree (MST)](https://en.wikipedia.org/wiki/Minimum_spanning_tree)
    * Kruskal
    * Prim
* [Traveling salesman problem (TSP)](https://en.wikipedia.org/wiki/Travelling_salesman_problem)
    * Bruteforce algorithm
    * Minimum spanning tree heuristic (TSP MST heuristic)
    * Nearest neighbor heuristic (NN heuristic)
* Maximum flow
    * [Edmonds-Karp](https://en.wikipedia.org/wiki/Edmonds%E2%80%93Karp_algorithm)
* Minimum cost flow (MCF)
    * Cycle canceling
    * Successive shortest path
* Maximum matching
    * Flow algorithm

See [graphp/algorithms](https://github.com/graphp/algorithms) for more details.

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org/).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require clue/graph:^0.9.3
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 8+ and
HHVM.
It's *highly recommended to use the latest supported PHP version* for this project.

You may also want to install some of the [additional components](#components).
A list of all official components can be found in the [graphp project](https://github.com/graphp).

## Tests

This library uses PHPUnit for its extensive test suite.
To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org/):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ vendor/bin/phpunit
```

## Contributing

This library comes with an extensive test suite and is regularly tested and used in the *real world*.
Despite this, this library is still considered beta software and its API is subject to change.
The [changelog](CHANGELOG.md) lists all relevant information for updates between releases.

If you encounter any issues, please don't hesitate to drop us a line, file a bug report or even best provide us with a patch / pull request and/or unit test to reproduce your problem.

Besides directly working with the code, any additional documentation, additions to our readme or even fixing simple typos are appreciated just as well.

Any feedback and/or contribution is welcome!

Check out #graphp on irc.freenode.net.

## License

This project is released under the permissive [MIT license](LICENSE).

> Did you know that I offer custom development services and issuing invoices for
  sponsorships of releases and for contributions? Contact me (@clue) for details.

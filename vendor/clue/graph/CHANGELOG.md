# Changelog

## 0.9.3 (2021-12-30)

*   Feature: Support PHP 8.1 release.
    (#208 by @clue)

*   Fix: Fix automatic vertex ID generation when using vertex IDs with strings.
    (#204 by @viktorprogger)

*   Improve test suite and use GitHub Actions for continuous integration (CI).
    (#207 by @clue)

## 0.9.2 (2020-12-03)

*   Feature: Support PHP 8 and PHPUnit 9.3.
    (#200 by @SimonFrings)

## 0.9.1 (2019-10-02)

*   Fix: Deleting vertex with loop edge no longer fails.
    (#149 by @tomzx)

*   Fix: Fix returning directed loop edges and adjacent vertices from vertex twice.
    (#170 by @clue)

*   Minor documentation updates and fixes.
    (#153 by @marclaporte and #163, #164 and #172 by @clue)

*   Improve test suite to move tests to `Fhaculty\Graph\Tests` namespace,
    update test suite to support PHPUnit 6 and PHPUnit 5 and
    support running on legacy PHP 5.3 through PHP 7.2 and HHVM.
    (#148 by @tomzx and #150 and #162 by @clue)

*   Originally planned to add a new `AttributeAware::removeAttribute()` method,
    but reverted due to BC break. Change will be reconsidered for next major release.
    (#138 and #171 by @johnathanmdell and @clue)

## 0.9.0 (2015-03-07)

*   BC break: Split off individual components in order to stabilize core graph lib.
    ([#120](https://github.com/clue/graph/issues/120))

    *   Split off `Algorithm` namespace into separate [graphp/algorithms](https://github.com/graphp/algorithms) package.
        ([#119](https://github.com/clue/graph/issues/119))

    *   Split off `Exporter\TrivialGraphFormat` into separate [graphp/trivial-graph-format](https://github.com/graphp/trivial-graph-format) package.
        ([#121](https://github.com/clue/graph/issues/121))

    *   Split off `Loader` namespace into separate [graphp/plaintext](https://github.com/graphp/plaintext) package.
        ([#117](https://github.com/clue/graph/issues/117))

*   BC break: Remove Exporter from `Graph` and `Graph::__toString()` (trivial graph format exporter has been split off).
    ([#122](https://github.com/clue/graph/pull/122))

*   BC break: Vertices can no longer be sorted by (in/out)degree (degree algorithm has been split off).
    ([#128](https://github.com/clue/graph/pull/128))

*   Apply PSR-4 layout under `src/` and add tests to achieve 100% test coverage.
    ([#127](https://github.com/clue/graph/issues/127) & [#129](https://github.com/clue/graph/issues/129))

## 0.8.0 (2014-12-31)

*   Feature: Add general purpose Attributes.
    ([#103](https://github.com/clue/graph/pull/103))

*   BC break: Split off all GraphViz-related classes to a separate
    [graphp/graphviz](https://github.com/graphp/graphviz) package.
    ([#115](https://github.com/clue/graph/pull/115))

*   Feature: The base `Graph`, `Vertex` and `EdgeBase` classes can now be
    extended in order to implement a custom behavior. As such, one can now also
    instantiate them using the normal `new` operator instead of having to use
    `Graph::createVertex()` family of methods.
    ([#82](https://github.com/clue/graph/issues/82))

*   BC break: Rename `Algorithm\Directed::isDirected()` to remove its ambiguity
    in regards to mixed and/or empty graphs
    ([#80](https://github.com/clue/graph/issues/80))

    | Old name | New name |
    |---|---|
    | `Algorithm\Directed::isDirected()` | `Algorithm\Directed::hasDirected()` |

*   Feature: Add new `Algorithm\Directed::hasUndirected()` and
    `Algorithm\Directed::isMixed()` in order to complement the renamed
    `Algorithm\Directed::hasDirected()`
    ([#80](https://github.com/clue/graph/issues/80))

*   BC break: `Walk::factoryCycleFromVertices()` no longer tries to auto-complete
    a cycle if the first vertex does not match the last one, but now throws an
    `InvalidArgumentException` instead ([#87](https://github.com/clue/graph/issues/87))

*   Feature: Support loop `Walk`s, i.e. a walk with only a single edge from
    vertex A back to A ([#87](https://github.com/clue/graph/issues/87))

*   Fix: Stricter checks for invalid cycles, such as one with an invalid
    predecessor-map or no edges at all ([#87](https://github.com/clue/graph/issues/87))

*   Fix: The `Algorithm\ShortestPath\MooreBellmanFord` now also works for unweighted
    edges. This also fixes an issue where `Algorithm\DetectNegativeCycle` didn't work
    for unweighted edges. ([#81](https://github.com/clue/graph/issues/81))

*   Fix: The `Algorithm\MinimumCostFlow` algorithms now work again. The reference
    to a non-existant class has been updated. Also fixed several issues with
    regards to special cases such as disconnected or undirected graphs.
    ([#74](https://github.com/clue/graph/issues/74))

*   BC break: Remove unneeded alias definitions of `getVertexFirst()`,
    `getVertexSource()` and `getVertexTarget()`
    ([#76](https://github.com/clue/graph/issues/76)):

    | Old name | New name |
    |---|---|
    | `Graph::getVertexFirst()` | `Graph::getVertices()->getVertexFirst()` |
    | `Walk::getVertexSource()` | `Walk::getVertices()->getVertexFirst()` |
    | `Walk::getVertexTarget()` | `Walk::getVertices()->getVertexLast()` |

## 0.7.1 (2014-03-12)

*   Fix: Throwing an `UnexpectedValueException` if writing GraphViz Dot script
    to a temporary file fails and remove its debugging output
    ([#77](https://github.com/clue/graph/issues/77) and [#78](https://github.com/clue/graph/issues/78) @Metabor)

*   Fix: Improved GraphViz support for MS Windows
    ([#99](https://github.com/clue/graph/issues/99))

## 0.7.0 (2013-09-11)

*   Feature: Add new `Set\Vertices` and `Set\Edges` classes that handle common
    operations on a Set of multiple `Vertex` and `Edge` instances respectively.
    ([#48](https://github.com/clue/graph/issues/48))

*   BC break: Move operations and their corresponding constants concerning Sets
    to their corresponding Sets:

    | Old name | New name |
    |---|---|
    | `Edge\Base::getFirst()` | `Set\Edges::getEdgeOrder()` |
    | `Edge\Base::getAll()` | `Set\Edges::getEdgesOrder()` |
    | `Edge\Base::ORDER_*` | `Set\Edges::ORDER_*` |
    |---|---|
    | `Vertex::getFirst()` | `Set\Vertices::getVertexOrder()` |
    | `Vertex::getAll()` | `Set\Vertices::getVerticesOrder()` |
    | `Vertex::ORDER_` | `Set\Vertices::ORDER_*` |

*   BC break: Each `getVertices*()` and `getEdges*()` method now returns a `Set`
    instead of a primitive array of instances. *Most* of the time this should
    work without changing your code, because each `Set` implements an `Iterator`
    interface and can easily be iterated using `foreach`. However, using a `Set`
    instead of a plain array differs when checking its boolean value or
    comparing two Sets. I.e. if you happen to want to check if an `Set` is empty,
    you now have to use the more explicit syntax `$set->isEmpty()`.
    
*   BC break: `Vertex::getVertices()`, `Vertex::getVerticesEdgeTo()` and
    `Vertex::getVerticesEdgeFrom()` now return a `Set\Vertices` instance that
    may contain duplicate vertices if parallel (multiple) edges exist. Previously
    there was no easy way to detect this situation - this is now the default. If
    you also want to get unique / distinct `Vertex` instances, use
    `Vertex::getVertices()->getVerticesDistinct()` where applicable.

*   BC break: Remove all occurances of `getVerticesId()`, use
    `getVertices()->getIds()` instead.

*   BC break: Merge `Cycle` into `Walk` ([#61](https://github.com/clue/graph/issues/61)).
    As such, its static factory methods had to be renamed. Update your references if applicable:

    | Old name | New name |
    |---|---|
    | `Cycle::factoryFromPredecessorMap()` | `Walk::factoryCycleFromPredecessorMap()` |
    | `Cycle::factoryFromVertices()` | `Walk::factoryCycleFromVertices()` |
    | `Cycle::factoryFromEdges()` | `Walk::factoryCycleFromEdges()` |

*   BC break: Remove `Graph::isEmpty()` because it's not well-defined and might
    be confusing. Most literature suggests it should check for existing edges,
    whereas the old behavior was to check for existing vertices instead. Use either
    of the new and more transparent methods
    `Algorithm\Property\GraphProperty::isNull()` (old behavior) or (where applicable)
    `Algorithm\Property\GraphProperty::isEdgeless()` ([#63](https://github.com/clue/graph/issues/63)).

*   BC break: Each of the above methods (`Walk::factoryCycleFromPredecessorMap()`,
    `Walk::factoryCycleFromVertices()`, `Walk::factoryCycleFromEdges()`) now
    actually makes sure the returned `Walk` instance is actually a valid Cycle,
    i.e. the start `Vertex` is the same as the end `Vertex` ([#61](https://github.com/clue/graph/issues/61))

*   BC break: Each `Algorithm\ShortestPath` algorithm now consistenly does not
    return a zero weight for the root Vertex and now supports loop edges on the root
    Vertex ([#62](https://github.com/clue/graph/issues/62))

*   BC break: Each `Algorithm\ShortestPath` algorithm now consistently throws an
    `OutOfBoundsException` for unreachable vertices
    ([#62](https://github.com/clue/graph/issues/62))

*   BC break: A null Graph (a Graph with no Vertices and thus no Edges) is not a
    valid tree (because it is not connected), adjust `Algorithm\Tree\Base::isTree()`
    accordingly.
    ([#72](https://github.com/clue/graph/issues/72))

*   BC break: Remove all occurances of `getNumberOfVertices()` and
    `getNumberOfEdges()` ([#75](https://github.com/clue/graph/issues/75) and
    [#48](https://github.com/clue/graph/issues/48)):

    | Old name | New name |
    |---|---|
    | `$set->getNumberOfVertices()` | `count($set->getVertices())` |
    | `$set->getNumberOfEdges()` | `count($set->getEdges())` |
    
*   BC break: Replace base `Set` class with `Set\DualAggregate` interface. This
    is unlikely to affect you, but might potentially break your custom
    inheritance or polymorphism for algorithms.
    ([#75](https://github.com/clue/graph/issues/75))

*   Feature: Add `Algorithm\ShortestPath\Base::hasVertex(Vertex $vertex)` to check whether
    a path to the given Vertex exists ([#62](https://github.com/clue/graph/issues/62)).

*   Feature: Support opening GraphViz images on Mac OS X in default image viewer
    ([#67](https://github.com/clue/graph/issues/67) @onigoetz)

*   Feature: Add `Algorithm\MinimumSpanningTree\Base::getWeight()` to get total
    weight of resulting minimum spanning tree (MST).
    ([#73](https://github.com/clue/graph/issues/73))

*   Feature: Each `Algorithm\MinimumSpanningTree` algorithm now supports
    undirected and mixed Graphs, as well as null weights for Edges.
    ([#73](https://github.com/clue/graph/issues/73))

*   BC break: Each `Algorithm\MinimumSpanningTree` algorithm now throws an
    `UnexpectedValueException` for unconnected Graphs (and thus also null Graphs).
    ([#73](https://github.com/clue/graph/issues/73))

*   Feature: Add `Walk::factoryFromVertices()`
    ([#64](https://github.com/clue/graph/issues/64)).

*   Fix: Checking `Walk::isValid()`
    ([#61](https://github.com/clue/graph/issues/61))

*   Fix: Missing import prevented
    `Algorithm\ShortestPath\MooreBellmanFord::getCycleNegative()` from actually
    throwing the right `UnderflowException` if no cycle was found
    ([#62](https://github.com/clue/graph/issues/62))

*   Fix: Calling `Exporter\Image::setFormat()` had no effect due to misassignment
    ([#70](https://github.com/clue/graph/issues/70) @FGM)

## 0.6.0 (2013-07-11)

*   BC break: Move algorithm definitions in base classes to separate algorithm classes ([#27](https://github.com/clue/graph/issues/27)).
    The following methods containing algorithms were now moved to separate algorithm classes. This
    change encourages code-reuse, simplifies spotting algorithms, helps reducing complexity,
    improves testablity and avoids tight coupling. Update your references if applicable:

    | Old name | New name | Related ticket |
    |---|---|---|
    | `Set::getWeight()` | `Algorithm\Weight::getWeight()` | [#33](https://github.com/clue/graph/issues/33) |
    | `Set::getWeightFlow()` | `Algorithm\Weight::getWeightFlow()` | [#33](https://github.com/clue/graph/issues/33) |
    | `Set::getWeightMin()` | `Algorithm\Weight::getWeightMin()` | [#33](https://github.com/clue/graph/issues/33) |
    | `Set::isWeighted()` | `Algorithm\Weight::isWeighted()` | [#33](https://github.com/clue/graph/issues/33) |
    |-|-|-|
    | `Graph::getDegree()` | `Algorithm\Degree::getDegree()` | [#29](https://github.com/clue/graph/issues/29) |
    | `Graph::getDegreeMin()` | `Algorithm\Degree::getDegreeMin()` | [#29](https://github.com/clue/graph/issues/29) |
    | `Graph::getDegreeMax()` | `Algorithm\Degree::getDegreeMax()` | [#29](https://github.com/clue/graph/issues/29) |
    | `Graph::isRegular()` | `Algorithm\Degree::isRegular()` | [#29](https://github.com/clue/graph/issues/29) |
    | `Graph::isBalanced()` | `Algorithm\Degree::isBalanced()` | [#29](https://github.com/clue/graph/issues/29) |
    | `Vertex::getDegree()` | `Algorithm\Degree:getDegreeVertex()` | [#49](https://github.com/clue/graph/issues/49) |
    | `Vertex::getDegreeIn()` | `Algorithm\Degree:getDegreeInVertex()` | [#49](https://github.com/clue/graph/issues/49) |
    | `Vertex::getDegreeOut()` | `Algorithm\Degree:getDegreeOutVertex()` | [#49](https://github.com/clue/graph/issues/49) |
    | `Vertex::isSink()` | `Algorithm\Degree:isVertexSink()` | [#49](https://github.com/clue/graph/issues/49) |
    | `Vertex::isSource()` | `Algorithm\Degree:isVertexSource()` | [#49](https://github.com/clue/graph/issues/49) |
    | `Vertex::isIsolated()` | `Algorithm\Degree::isVertexIsolated()` | [#49](https://github.com/clue/graph/issues/49) |
    |-|-|-|
    | `Set::isDirected()` | `Algorithm\Directed::isDirected()` | [#34](https://github.com/clue/graph/issues/34) |
    |-|-|-|
    | `Graph::isSymmetric()` | `Algorithm\Symmetric::isSymmetric()` | [#41](https://github.com/clue/graph/issues/41) |
    |-|-|-|
    | `Graph::isComplete()` | `Algorithm\Complete::isComplete()` | [#43](https://github.com/clue/graph/issues/43) |
    |-|-|-|
    | `Set::hasFlow()` | `Algorithm\Flow::hasFlow()` | [#47](https://github.com/clue/graph/issues/47) |
    | `Graph::getBalance()` | `Algorithm\Flow::getBalance()` | [#30](https://github.com/clue/graph/issues/30), [#47](https://github.com/clue/graph/issues/47) |
    | `Graph::isBalancedFlow()` | `Algorithm\Flow::isBalancedFlow()` | [#30](https://github.com/clue/graph/issues/39), [#47](https://github.com/clue/graph/issues/47) |
    | `Vertex::getFlow()` | `Algorithm\Flow::getFlowVertex()` | [#47](https://github.com/clue/graph/issues/47) |
    |-|-|-|
    | `Vertex::isLeaf()` | `Algorithm\Tree\Undirected::isVertexLeaf()` | [#44](https://github.com/clue/graph/issues/44) |
    |-|-|-|
    | `Set::hasLoop()` | `Algorithm\Loop::hasLoop()` | [#51](https://github.com/clue/graph/issues/51) |
    | `Vertex::hasLoop()` | `Algorithm\Loop::hasLoopVertex()` | [#51](https://github.com/clue/graph/issues/51) |
    |-|-|-|
    | `Set::hasEdgeParallel()` | `Algorithm\Parallel::hasEdgeParallel()` | [#52](https://github.com/clue/graph/issues/52) |
    | `Edge\Base::hasEdgeParallel()` | `Algorithm\Parallel::hasEdgeParallelEdge()` | [#52](https://github.com/clue/graph/issues/52) |
    | `Edge\Base::getEdgesParallel()` | `Algorithm\Parallel::getEdgeParallelEdge()` | [#52](https://github.com/clue/graph/issues/52) |
    |-|-|-|
    | `Graph::isEdgeless()` | `Algorithm\Property\GraphProperty::isEdgeless()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Graph::isTrivial()` | `Algorithm\Property\GraphProperty::isTrivial()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isCycle()` | `Algorithm\Property\WalkProperty::isCycle()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isPath()` | `Algorithm\Property\WalkProperty::isPath()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::hasCycle()` | `Algorithm\Property\WalkProperty::hasCycle()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isLoop()` | `Algorithm\Property\WalkProperty::isLoop()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isDigon()` | `Algorithm\Property\WalkProperty::isDigon()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isTriangle()` | `Algorithm\Property\WalkProperty::isTriangle()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isSimple()` | `Algorithm\Property\WalkProperty::isSimple()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isHamiltonian()` | `Algorithm\Property\WalkProperty::isHamiltonian()` | [#54](https://github.com/clue/graph/issues/54) |
    | `Walk::isEulerian()` | `Algorithm\Property\WalkProperty::isEulerian()` | [#54](https://github.com/clue/graph/issues/54) |

*   BC break: Remove unneeded algorithm alias definitions ([#31](https://github.com/clue/graph/issues/31), [#50](https://github.com/clue/graph/issues/50)). The following *alias definitions*
    have been removed, their original/actual name has already existed before and continues to work
    unchanged. Update your references if applicable:

    | Old/removed alias definition | Actual name |
    |---|---|
    | `Graph::isConnected()` | `Algorithm\ConnectedComponents::isSingle()` |
    | `Graph::hasEulerianCycle()` | `Algorithm\Eulerian::hasCycle()` |
    | `Graph::getNumberOfComponents()` | `Algorithm\ConnectedComponents::getNumberOfComponents()` |
    | `Graph::getNumberOfGroups()` | `Algorithm\Groups::getNumberOfGroups()` |
    | `Graph::isBipartit()` | `Algorithm\Bipartit::isBipartit()` |
    | `Vertex::hasPathTo()` | `Algorithm\ShortestPath\BreadthFirst::hasVertex()` |
    | `Vertex::hasPathFrom()` | `Algorithm\ShortestPath\BreadthFirst::hasVertex()` |
    | `Vertex::getVerticesPathTo()` | `Algorithm\ShortestPath\BreadthFirst::getVertices()` |
    | `Vertex::getVerticesPathFrom()` | `Algorithm\ShortestPath\BreadthFirst::getVertices()` |

*   BC break: `Graph::createVertices()` now returns an array of vertices instead of the
    chainable `Graph` ([#19](https://github.com/clue/graph/issues/19))

*   BC break: Move `Loader\UmlClassDiagram` to separate [fhaculty/graph-uml](https://github.com/fhaculty/graph-uml)
    repo ([#38](https://github.com/clue/graph/issues/38))

*   BC break: Remove needless `Algorithm\MinimumSpanningTree\PrimWithIf`
    (use `Algorithm\MinimumSpanningTree\Prim` instead)
    ([#45](https://github.com/clue/graph/issues/45))

*   BC break: `Vertex::createEdgeTo()` now returns an instance of type
    `Edge\Undirected` instead of `Edge\UndirectedId`
    ([#46](https://github.com/clue/graph/issues/46))

*   BC break: `Edge\Base::setCapacity()` now consistently throws an `RangeException`
    instead of `InvalidArgumentException` if the current flow exceeds the new maximum
    capacity ([#53](https://github.com/clue/graph/issues/53))

*   Feature: New `Algorithm\Tree` namespace with algorithms for undirected and directed,
    rooted trees ([#44](https://github.com/clue/graph/issues/44))

*   Feature: According to be above list of moved algorithm methods, the following algorithm
    classes have been added ([#27](https://github.com/clue/graph/issues/27)):
    *   New `Algorithm\Weight` ([#33](https://github.com/clue/graph/issues/33))
    *   New `Algorithm\Degree` ([#29](https://github.com/clue/graph/issues/29), [#49](https://github.com/clue/graph/issues/49))
    *   New `Algorithm\Directed` ([#34](https://github.com/clue/graph/issues/34))
    *   New `Algorithm\Symmetric` ([#41](https://github.com/clue/graph/issues/41))
    *   New `Algorithm\Complete` ([#43](https://github.com/clue/graph/issues/43))
    *   New `Algorithm\Flow` ([#30](https://github.com/clue/graph/issues/30), [#47](https://github.com/clue/graph/issues/47))
    *   New `Algorithm\Tree` ([#44](https://github.com/clue/graph/issues/44))
    *   New `Algorithm\Loop` ([#51](https://github.com/clue/graph/issues/51))
    *   New `Algorithm\Parallel` ([#52](https://github.com/clue/graph/issues/52))
    *   New `Algorithm\Property` ([#54](https://github.com/clue/graph/issues/54))

*   Feature: `Graph::createVertices()` now also accepts an array of vertex IDs
    ([#19](https://github.com/clue/graph/issues/19))

*   Feature: Add `Algorithm\Property\WalkProperty::hasLoop()` alias definition for
    completeness ([#54](https://github.com/clue/graph/issues/54))

*   Feature: Add `Algorithm\Property\WalkProperty::isCircuit()` definition to distinguish
    circuits from cycles ([#54](https://github.com/clue/graph/issues/54))

*   Fix: Checking hamiltonian cycles always returned false
    ([#54](https://github.com/clue/graph/issues/54))

*   Fix: A Walk with no edges is no longer considered a valid cycle
    ([#54](https://github.com/clue/graph/issues/54))

*   Fix: Various issues with `Vertex`/`Edge` layout attributes
    ([#32](https://github.com/clue/graph/issues/32))

*   Fix: Getting multiple parallel edges for undirected edges
    ([#52](https://github.com/clue/graph/issues/52))

## 0.5.0 (2013-05-07)

*   First tagged release (See issue [#20](https://github.com/clue/graph/issues/20) for more info on why it starts as v0.5.0)

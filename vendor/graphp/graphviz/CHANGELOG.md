# Changelog

## 0.2.2 (2019-10-04)

*   Feature: Omit root graph name unless explicitly assigned via `graphviz.name` attribute.
    (#28 by @rhelms and @clue)

    ```php
    $graph = new Graph();
    $graph->setAttribute('graphviz.name', 'g');
    ```

*   Feature: Remove unneeded dependency on `graphp/algorithms`.
    (#39 by @clue)

*   Feature / Fix: Use UTF-8 encoding (Unicode) by default and respect charset attribute.
    (#27 by @Ithilias and @clue)

*   Fix: Fix representing directed loop edges as directed edge
    (#37 by @clue)

*   Add examples and documentation for GraphViz attributes, labels and record shapes.
    (#26 by @clue)

*   Update test suite to support PHPUnit 6 and PHPUnit 5 and support running on legacy PHP 5.3 through PHP 7.2 and HHVM.
    (#24 by @clue)

## 0.2.1 (2015-03-08)

*   Support graph v0.9 (while keeping BC)
    ([#9](https://github.com/graphp/graphviz/pull/9))

## 0.2.0 (2015-01-19)

*   BC break: Refactor to inject Graph into GraphViz on demand, inject GraphViz into exporters
    ([#6](https://github.com/graphp/graphviz/pull/6))

*   BC break: Remove legacy layout helper
    ([#5](https://github.com/graphp/graphviz/pull/5))

## 0.1.0 (2014-12-31)

*   First tagged release, split off from [clue/graph](https://github.com/clue/graph) v0.8.0
    ([#1](https://github.com/graphp/graphviz/issues/1))

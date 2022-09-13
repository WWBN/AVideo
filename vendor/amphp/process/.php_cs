<?php

$config = new Amp\CodeStyle\Config;
$config->getFinder()
    ->in(__DIR__ . '/examples')
    ->in(__DIR__ . '/lib')
    ->in(__DIR__ . '/test');

$config->setCacheFile(__DIR__ . '/.php_cs.cache');

return $config;

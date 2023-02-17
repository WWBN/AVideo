<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->append(['.php_cs'])
    ->notName('PhpStormStubsMap.php')
;

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

return PhpCsFixer\Config::create()
    ->setRules([
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'constant_case' => true
        // 'unix_line_endings' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile($cacheDir . '/.php_cs.cache')
;

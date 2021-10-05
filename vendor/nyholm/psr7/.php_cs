<?php

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array('syntax' => 'short'),
        'native_function_invocation' => true,
        'native_constant_invocation' => true,
        'ordered_imports' => true,
        'declare_strict_types' => true,
        'single_import_per_statement' => false,
        'concat_space' => ['spacing'=>'one'],
        'phpdoc_align' => ['align'=>'left'],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->name('*.php')
    )
;

return $config;

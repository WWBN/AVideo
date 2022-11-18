<?php

$finder = PhpCsFixer\Finder::create()->in(['src', 'tests']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'not_operator_with_space' => true,
        'single_quote' => true,
        'binary_operator_spaces' => ['operators' => ['=' => 'align_single_space']],
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);

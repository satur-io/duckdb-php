<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/test/Integration')
    ->in(__DIR__ . '/test/Unit')
    ->in(__DIR__ . '/test/Benchmark')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
    ;

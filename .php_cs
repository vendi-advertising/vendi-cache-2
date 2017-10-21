<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src/')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'cast_spaces' => ['space' => 'single'],
        'class_keyword_remove' => true,
        'declare_strict_types' => true,
        'lowercase_cast' => true,
        'mb_str_functions' => true,
        'method_separation' => true,
        'no_empty_statement' => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
;

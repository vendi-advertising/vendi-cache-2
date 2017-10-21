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
    ])
    ->setFinder($finder)
    ->setUsingCache(false)
;

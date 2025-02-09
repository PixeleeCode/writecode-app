<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->exclude('var')
    ->append([__FILE__])
;

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@Symfony' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => false,
        ],
    ])
    ->setFinder($finder)
;

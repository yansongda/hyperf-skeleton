<?php

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'class_attributes_separation' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'line_ending' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('grpc')
            ->exclude('storage')
            ->exclude('public')
            ->exclude('runtime')
            ->exclude('vendor')
            ->exclude('test')
            ->in(__DIR__)
    );

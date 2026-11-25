<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app/DTOs',
        __DIR__ . '/app/Enums',
        __DIR__ . '/app/Exceptions',
        __DIR__ . '/app/Http/Controllers/Api',
        __DIR__ . '/app/Http/Requests',
        __DIR__ . '/app/Http/Resources',
        __DIR__ . '/app/Models',
        __DIR__ . '/app/Policies',
        __DIR__ . '/app/Repositories',
        __DIR__ . '/app/Services',
        __DIR__ . '/tests',
    ])
    ->append([
        __DIR__ . '/app/Http/Middleware/Cors.php',
        __DIR__ . '/app/Providers/RepositoryServiceProvider.php',
        __DIR__ . '/routes/api.php',
    ]);

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'strict_comparison' => true,
    ])
    ->setFinder($finder);

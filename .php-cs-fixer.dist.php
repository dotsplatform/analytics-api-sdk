<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

/*
 * Laravel Shift style (same base as notifications-api-sdk, reports-api-sdk and the analytics service) plus a strict layer.
 */

$projectPath = getcwd();

$finder = Finder::create()
    ->in([
        $projectPath.'/src',
        $projectPath.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

/** @var Config $config */
$config = ShiftCS\styles($finder);

$strictRules = [
    'declare_strict_types' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    '@PHP8x3Migration' => true,
    '@PHP8x3Migration:risky' => true,
    'void_return' => true,
    'no_unused_imports' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
    'fully_qualified_strict_types' => true,
    'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
    'native_function_invocation' => ['include' => [], 'strict' => true],
    'phpdoc_to_return_type' => true,
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
];

return $config
    ->setRiskyAllowed(true)
    ->setRules(array_merge($config->getRules(), $strictRules))
    ->setCacheFile($projectPath.'/.php-cs-fixer.cache');

<?php

// Used for the Symfony 4.4 -> 7.4 upgrade: converts annotations to PHP
// attributes (routes, Doctrine mapping, Gedmo, validation) and applies the
// Symfony and Doctrine API renames. Run: vendor/bin/rector process

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([__DIR__ . '/tests/e2e'])
    ->withImportNames(importShortClasses: false)
    ->withAttributesSets(symfony: true, doctrine: true, gedmo: true, phpunit: true)
    // PHP 8.2-8.4 deprecations: "${var}" in strings, implicitly nullable parameters
    ->withRules([VariableInStringInterpolationFixerRector::class, ExplicitNullableParamTypeRector::class])
    // Upgrade rules for the installed Symfony, Doctrine, Twig and PHPUnit versions
    ->withComposerBased(symfony: true, doctrine: true, twig: true, phpunit: true);

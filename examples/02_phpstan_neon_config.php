<?php

declare(strict_types=1);

/**
 * Example 2: Generating a phpstan.neon configuration file programmatically.
 *
 * Most PHPStan configuration is done through a NEON file (phpstan.neon or
 * phpstan.neon.dist). This example shows the structure of a comprehensive
 * configuration and explains each section.
 *
 * This script writes a ready-to-use phpstan.neon.dist to the current
 * working directory for inspection.
 *
 * Run from any directory that has phpstan available:
 *   php examples/02_phpstan_neon_config.php
 */

// -----------------------------------------------------------------------
// Configuration sections explained
// -----------------------------------------------------------------------

/**
 * NEON configuration template.
 *
 * Sections:
 *  - includes     : import other NEON files (e.g. vendor extensions, baseline)
 *  - parameters   : core analysis settings
 *  - services     : register custom rules, type extensions, etc.
 */
$neonContent = <<<'NEON'
# phpstan.neon.dist — committed to version control
# Developers can create a local phpstan.neon that includes this file and
# overrides settings (e.g. to temporarily lower the level).

includes:
    # Opt into experimental checks not yet enabled by default.
    - vendor/phpstan/phpstan/conf/bleedingEdge.neon

    # PHPUnit extension — teaches PHPStan about mock objects and assertion narrowing.
    # Requires: composer require --dev phpstan/phpstan-phpunit
    # - vendor/phpstan/phpstan-phpunit/extension.neon

    # Maintain a baseline of pre-existing errors to ignore while you raise the level.
    # Generate with: vendor/bin/phpstan analyse --generate-baseline
    # - phpstan-baseline.neon

parameters:
    # Strictness level: 0 (loose) to 10 (strictest). Start low and raise over time.
    level: 8

    # Directories to analyse. Relative to the location of this file.
    paths:
        - src/
        - tests/   # Analyse tests too — they are code and have bugs too.

    # Directories to exclude from analysis (generated code, vendored stubs, etc.)
    excludePaths:
        - src/Generated/
        - var/cache/

    # PHPStan version of your project's PHP target. Affects which functions and
    # types are available.
    phpVersion: 80300  # PHP 8.3 — numeric format: major * 10000 + minor * 100 + patch

    # Treat all classes as if they were final where no explicit 'extends' is present.
    # Enables tighter type narrowing.
    checkMissingIterableValueType: true

    # Report properties that are never read.
    checkUninitializedProperties: true

    # Report use of dynamic properties (PHP 8.2+ deprecates them by default).
    reportUnmatchedIgnoredErrors: true

    # Ignore errors by identifier, message pattern, or file.
    # Prefer identifier-based ignores for stability across refactoring.
    ignoreErrors:
        # Ignore a specific error by identifier (stable across code changes).
        - identifier: missingType.generics

        # Ignore an error pattern in a specific file (use sparingly).
        - message: '#^Call to an undefined method#'
          path: tests/LegacyTest.php
          count: 1   # Fail if the error count changes — prevents silent suppression.

    # Stubs override type information from vendor packages that have incorrect types.
    stubFiles:
        - stubs/SomeVendorClass.stub.php

    # Universal object crates: properties can be set dynamically (legacy code).
    universalObjectCratesClasses:
        - Illuminate\Database\Eloquent\Model  # Example: Laravel Eloquent models

NEON;

// -----------------------------------------------------------------------
// Write the file
// -----------------------------------------------------------------------

$outputPath = getcwd() . '/phpstan.neon.dist.example';
file_put_contents($outputPath, $neonContent);

echo "=== Written to: {$outputPath} ===\n\n";
echo $neonContent;
echo "\n";
echo "To use this config:\n";
echo "  cp {$outputPath} phpstan.neon.dist\n";
echo "  vendor/bin/phpstan analyse\n";

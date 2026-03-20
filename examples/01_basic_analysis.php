<?php

declare(strict_types=1);

/**
 * Example 1: Running PHPStan programmatically.
 *
 * PHPStan is primarily a CLI tool:
 *
 *   vendor/bin/phpstan analyse src/ --level=8
 *
 * This example shows how to invoke it from PHP code (e.g. inside a CI
 * pipeline script), capture the output, and act on the exit code.
 *
 * Run from the repo root:
 *   php examples/01_basic_analysis.php
 */

// PHPStan ships as a PHAR, so no vendor/autoload.php is available in
// this distribution repo. In a project that requires phpstan/phpstan
// via Composer, use: require __DIR__ . '/../vendor/autoload.php';

// -----------------------------------------------------------------------
// 1. Create a temporary PHP file with known type errors
// -----------------------------------------------------------------------

$tempDir = sys_get_temp_dir() . '/phpstan_example_' . uniqid('', true);
mkdir($tempDir, 0777, true);

$badFile = $tempDir . '/bad_code.php';
file_put_contents($badFile, <<<'PHP'
<?php

declare(strict_types=1);

/**
 * This file intentionally contains type errors for demonstration purposes.
 */

function greet(string $name): string
{
    return 'Hello, ' . $name;
}

// Error 1: passing an int where string is expected
$greeting = greet(42);

// Error 2: calling a method that does not exist
$result = $greeting->toUpperCase();

// Error 3: accessing an undefined variable
echo $undefined;
PHP);

// -----------------------------------------------------------------------
// 2. Build the PHPStan command
//
//    When phpstan/phpstan is installed via Composer, the binary is at
//    vendor/bin/phpstan. In this distribution repo it is at ./phpstan.
// -----------------------------------------------------------------------

$phpstanBin = file_exists(__DIR__ . '/../vendor/bin/phpstan')
    ? __DIR__ . '/../vendor/bin/phpstan'
    : __DIR__ . '/../phpstan'; // PHAR entry point

$command = implode(' ', [
    PHP_BINARY,
    escapeshellarg($phpstanBin),
    'analyse',
    escapeshellarg($badFile),
    '--level=8',
    '--no-progress',
    '--error-format=table',
    '2>&1',
]);

// -----------------------------------------------------------------------
// 3. Run PHPStan and capture the output
// -----------------------------------------------------------------------

echo "=== Running PHPStan ===\n";
echo "Command: {$command}\n\n";

$output   = [];
$exitCode = 0;
exec($command, $output, $exitCode);

echo implode("\n", $output) . "\n\n";

// -----------------------------------------------------------------------
// 4. Interpret the exit code
//
//    0 — no errors found
//    1 — analysis errors found
//    2 — command-line or configuration error
// -----------------------------------------------------------------------

if ($exitCode === 0) {
    echo "PHPStan found no errors.\n";
} elseif ($exitCode === 1) {
    echo "PHPStan found errors (exit code 1).\n";
    echo "Fix the errors above before merging.\n";
} else {
    echo "PHPStan encountered a configuration or internal error (exit code {$exitCode}).\n";
}

// -----------------------------------------------------------------------
// 5. Clean up
// -----------------------------------------------------------------------

unlink($badFile);
rmdir($tempDir);

<?php

declare(strict_types=1);

/**
 * Example 3: Writing a custom PHPStan rule.
 *
 * Custom rules let you enforce project-specific conventions that are not
 * covered by PHPStan's built-in checks.
 *
 * This file shows the skeleton of a rule that disallows the use of
 * var_dump() in production code. The rule is registered in phpstan.neon
 * and runs as part of the normal analysis pass.
 *
 * To use a custom rule in your project:
 *  1. Require phpstan/phpstan as a dev dependency.
 *  2. Place this file in src/PHPStan/ (or wherever your rules live).
 *  3. Register it in phpstan.neon (see REGISTRATION section below).
 *
 * This file intentionally avoids a composer autoload require so it can
 * be read as documentation without running phpstan/phpstan source.
 */

// -----------------------------------------------------------------------
// The custom rule class (place this in your source tree, e.g. src/PHPStan/)
// -----------------------------------------------------------------------

namespace MyApp\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Disallows the use of var_dump() outside of test files.
 *
 * var_dump() is a debugging aid that should never appear in committed
 * production code. PHPStan will report a violation on any call site.
 *
 * @implements Rule<FuncCall>
 */
final class NoVarDumpRule implements Rule
{
    /**
     * Returns the AST node type this rule analyses.
     *
     * PHPStan will only invoke {@see processNode()} for nodes of this type,
     * so returning FuncCall::class means the rule sees every function call.
     *
     * @return class-string<FuncCall>
     */
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * Analyses a single function call node and returns any errors.
     *
     * @param FuncCall $node  The function call node being inspected
     * @param Scope    $scope The current type scope (provides type information)
     *
     * @return list<RuleError> Zero or more errors to report at this call site
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Ignore dynamic calls like $fn() or $$name().
        if (!$node->name instanceof Name) {
            return [];
        }

        $functionName = $node->name->toLowerString();

        if ($functionName !== 'var_dump') {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Using var_dump() is not allowed in production code. '
                . 'Remove it before committing or use a proper logger instead.'
            )
                ->identifier('myapp.noVarDump') // machine-readable error ID for ignoreErrors
                ->build(),
        ];
    }
}

// -----------------------------------------------------------------------
// REGISTRATION in phpstan.neon
// -----------------------------------------------------------------------
//
// Add the following to your phpstan.neon or phpstan.neon.dist:
//
//   services:
//       -
//           class: MyApp\PHPStan\Rules\NoVarDumpRule
//           tags:
//               - phpstan.rules.rule
//
// To ignore a specific call site:
//
//   ignoreErrors:
//       - identifier: myapp.noVarDump
//         path: tests/DebugHelperTest.php
//
// -----------------------------------------------------------------------
// TESTING the rule
// -----------------------------------------------------------------------
//
// PHPStan provides a testing framework for rules via the
// phpstan/phpstan-testing package. A test looks like this:
//
//   use PHPStan\Testing\RuleTestCase;
//
//   class NoVarDumpRuleTest extends RuleTestCase
//   {
//       protected function getRule(): Rule
//       {
//           return new NoVarDumpRule();
//       }
//
//       public function testVarDumpIsDisallowed(): void
//       {
//           $this->analyse(
//               [__DIR__ . '/data/var_dump_usage.php'],
//               [
//                   ['Using var_dump() is not allowed in production code.', 5],
//               ]
//           );
//       }
//   }

echo "This file is documentation — it is not meant to be executed directly.\n";
echo "Read the comments above to understand how to write and register a custom rule.\n";

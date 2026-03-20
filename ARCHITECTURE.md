# PHPStan Architecture

## Purpose

PHPStan is a static analysis tool that finds type errors and other bugs in PHP code without executing it. It analyses PHP source files, infers types, and reports violations via a rich rule engine. This repository distributes the compiled PHAR and supporting infrastructure; the actual source lives at [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src).

## Repository Structure

```
phpstan.phar          — Compiled, self-contained binary (~26 MB PHAR archive)
phpstan               — Shell entry-point that loads phpstan.phar
bootstrap.php         — PHAR autoloader with PHP version polyfills
conf/
  bleedingEdge.neon   — Opt-in profile for experimental/unreleased rules
e2e/                  — End-to-end test scenarios executed against the PHAR
docker/               — Multi-arch Docker images for PHP 8.0–8.4
identifier-extractor/ — Tool to extract error identifiers from PHPStan source
playground-api/       — TypeScript AWS Lambda API for phpstan.org/try
playground-runner/    — PHP/Bref AWS Lambda that executes analysis for the playground
website/              — Static site source for phpstan.org (Eleventy + Vite + TailwindCSS)
```

## Key Design Decisions

- **PHAR distribution** — PHPStan ships as a single PHAR to avoid dependency conflicts with analysed projects. The source is written for PHP 8.1+ but is downgraded to PHP 7.4 syntax during the PHAR build.
- **Rule levels (0–10)** — Lower levels report only obvious bugs; higher levels add stricter type checks. Projects can adopt PHPStan incrementally by starting at level 0 and raising the level over time.
- **Error identifiers** — Every reported error has a machine-readable identifier (e.g., `argument.type`), allowing fine-grained baseline suppression via `ignoreErrors`.
- **Extension system** — PHPStan can be extended with custom rules, type extensions, and dynamic return type resolvers. Extensions are registered via NEON configuration.
- **Bleeding edge** — Experimental checks are gated behind `conf/bleedingEdge.neon` so they can be tested by early adopters before becoming defaults in the next major release.

## Extension Points

- **Custom rules** — Implement `PHPStan\Rules\Rule` in a separate package and register via a `phpstan.neon` extension.
- **Type extensions** — Implement `DynamicMethodReturnTypeExtension` or `DynamicFunctionReturnTypeExtension` to teach PHPStan about functions with context-dependent return types.
- **Type specifiers** — Implement `TypeSpecifierExtension` to express type narrowing from custom assertion functions.
- **Collectors** — Implement `Collector` to gather cross-file data for rules that require global knowledge.
- **Stubs** — Provide `.php` stub files to override or supplement type information for third-party libraries.

## Dependency Flow (inside the PHAR)

```
phpstan analyse (CLI command)
  └─► Analyser (orchestrates file analysis)
        ├─► Parser (PhpParser → AST)
        ├─► ScopeFactory (builds per-scope type environments)
        │     └─► TypeInferrer (propagates types through expressions)
        ├─► Rule[] (each rule inspects nodes and emits Errors)
        └─► ErrorFormatter (renders results as text / JSON / JUnit / etc.)
```

## Versioning Notes

- PHPStan 2.x is the current stable major version.
- The distribution package supports PHP ^7.4 | ^8.0 at runtime.
- The source (phpstan-src) requires PHP 8.1+ and is downgraded via `rector/rector` as part of the release pipeline.
- NEON configuration files use Nette Object Notation, a YAML superset.

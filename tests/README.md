# Distribution test suite

PHPUnit exercises `bootstrap.php` (`PHPStan\PharAutoloader`) and the `phpstan.phar` CLI entry points.

## PHPUnit PHAR (not committed)

From the repository root:

```bash
bash scripts/fetch-phpunit.sh
```

The script downloads `https://phar.phpunit.de/phpunit-9.6.38.phar` and its `.asc` signature, checks SHA-256 `6b5235f282c6ccab3674ac27c5b1ddbd1da838e74bf07002ddcfd030d31298d7`, and verifies the GPG signature against Sebastian Bergmann’s key fingerprint `D840 6D0D 8294 7747 2937 7831 4AA3 9408 6372 C20A`. It exits non-zero on any mismatch and only then writes `tools/phpunit-9.6.38.phar`.

## Gate (digest-pinned Docker)

PHP floor per `composer.json`: **7.4**. Also run on **8.0** (`^7.4|^8.0`).

Images:

- `php:7.4.33-cli@sha256:691f9ae2a3639de11d95f507bc29c723a7f27b79bdf91317aef8ded35f9864ce`
- `php:8.0.30-cli@sha256:5a3688ea73ab41ba0242172fed98952e83b6d202536729e33b58dc429db2db57`

On each image, run four times from the repo root (after `scripts/fetch-phpunit.sh`):

```bash
docker run --rm -v "$PWD":/workspace -w /workspace \
  php:7.4.33-cli@sha256:691f9ae2a3639de11d95f507bc29c723a7f27b79bdf91317aef8ded35f9864ce \
  php -d memory_limit=512M tools/phpunit-9.6.38.phar \
  --configuration phpunit.xml.dist --colors=never
```

Repeat the same command once more (default order), then twice with:

```bash
  --order-by=random --random-order-seed=20261008
```

Use the `php:8.0.30-cli@sha256:5a3688ea73ab41ba0242172fed98952e83b6d202536729e33b58dc429db2db57` image for the 8.0 matrix.

Expected: **30 tests**, **1 skipped** on 7.4, **2 skipped** on 8.0, **0 failures**.

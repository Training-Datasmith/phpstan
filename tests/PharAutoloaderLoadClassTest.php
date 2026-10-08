<?php declare(strict_types = 1);

final class PharAutoloaderLoadClassTest extends PharAutoloaderTestCase
{

	public function testLoadsShouldNotHappenExceptionFromPharSrc(): void
	{
		$result = $this->runAutoloadOnly();

		$this->assertSame(0, $result['exitCode']);
		$this->assertStringContainsString('ok-exception', trim($result['stdout']));
	}

	public function testLoadsBetterReflectionFromVendorTree(): void
	{
		$script = $this->scriptPrefix() . <<<'PHP'

PHPStan\PharAutoloader::loadClass('PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented');

if (!is_subclass_of('PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented', 'LogicException')) {
	fwrite(STDERR, 'not-logic-exception');
	exit(1);
}
echo 'ok-br';
PHP;

		$result = PhpScript::run($script);
		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('ok-br', trim($result['stdout']));
	}

	public function testMissingPhpstanClassReturnsCleanly(): void
	{
		$script = $this->scriptPrefix() . <<<'PHP'

PHPStan\PharAutoloader::loadClass('PHPStan\No\Such\ClassForAutoloadTest');

if (class_exists('PHPStan\No\Such\ClassForAutoloadTest', false)) {
	fwrite(STDERR, 'class-should-not-exist');
	exit(1);
}
if (!function_exists('array_first') || array_first([4]) !== 4) {
	fwrite(STDERR, 'polyfills-missing');
	exit(2);
}
echo 'ok-missing';
PHP;

		$result = PhpScript::run($script);
		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('ok-missing', trim($result['stdout']));
	}

	public function testInternalPrefixLoadsBundledIncludes(): void
	{
		$repoRoot = dirname(__DIR__);
		$script = '<?php declare(strict_types = 1);' . "\n\n";
		$script .= <<<'PHP'
if (function_exists('getallheaders')) {
	fwrite(STDERR, 'global-getallheaders-present');
	exit(1);
}

PHP;
		$script .= "require '{$repoRoot}/bootstrap.php';\n";
		$script .= <<<'PHP'
PHPStan\PharAutoloader::loadClass('_PHPStan_f63891026\No\Such');

if (!function_exists('_phpstan_f63891026\getallheaders')) {
	fwrite(STDERR, 'missing-getallheaders');
	exit(2);
}
if (!function_exists('_phpstan_f63891026\guzzlehttp\describe_type')) {
	fwrite(STDERR, 'missing-guzzle');
	exit(3);
}
if (!function_exists('_phpstan_f63891026\react\promise\resolve')) {
	fwrite(STDERR, 'missing-react');
	exit(4);
}
if (!class_exists('_PHPStan_f63891026\JetBrains\PHPStormStub\PhpStormStubsMap', false)) {
	fwrite(STDERR, 'missing-stubs-map');
	exit(5);
}
echo 'ok-internal';
PHP;

		$result = PhpScript::run($script);
		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('ok-internal', trim($result['stdout']));
	}

	/**
	 * @return array{exitCode: int, stdout: string, stderr: string}
	 */
	private function runAutoloadOnly(): array
	{
		$script = $this->scriptPrefix() . <<<'PHP'

PHPStan\PharAutoloader::loadClass('PHPStan\ShouldNotHappenException');

if (!is_subclass_of('PHPStan\ShouldNotHappenException', 'Exception')) {
	fwrite(STDERR, 'not-exception');
	exit(1);
}
$e = new \PHPStan\ShouldNotHappenException();
if ($e->getMessage() !== 'Internal error.') {
	fwrite(STDERR, 'wrong-message');
	exit(2);
}
echo 'ok-exception';
PHP;

		return PhpScript::run($script);
	}

	private function scriptPrefix(bool $withTrailingNewline = true): string
	{
		$repoRoot = dirname(__DIR__);
		$prefix = '<?php declare(strict_types = 1);' . "\n\n";
		$prefix .= "require '{$repoRoot}/bootstrap.php';\n";
		if ($withTrailingNewline) {
			$prefix .= "\n";
		}

		return $prefix;
	}

}

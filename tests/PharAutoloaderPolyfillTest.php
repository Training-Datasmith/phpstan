<?php declare(strict_types = 1);

final class PharAutoloaderPolyfillTest extends PharAutoloaderTestCase
{

	public function testPhp80PolyfillProvidesStrContains(): void
	{
		if (PHP_VERSION_ID >= 80000) {
			$this->markTestSkipped('PHP 8.0+ provides str_contains natively.');
		}

		$result = $this->runAutoloadScript(
			<<<'PHP'
if (!function_exists('str_contains')) {
	fwrite(STDERR, 'missing-str-contains-after');
	exit(2);
}
if (!str_contains('test', 't')) {
	fwrite(STDERR, 'str-contains-wrong');
	exit(3);
}
echo 'ok';
PHP
			,
			'',
			<<<'PHP'
if (function_exists('str_contains')) {
	fwrite(STDERR, 'native-str-contains');
	exit(1);
}
PHP
		);

		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('ok', trim($result['stdout']));
	}

	public function testPhp80PolyfillClassStaysUnloadedOnPhp80(): void
	{
		if (PHP_VERSION_ID < 80000) {
			$this->markTestSkipped('Only meaningful on PHP 8.0+.');
		}

		if (!function_exists('str_contains')) {
			$this->fail('str_contains must exist on PHP 8.0+.');
		}

		$result = $this->runAutoloadScript(<<<'PHP'
if (!str_contains('test', 'es')) {
	fwrite(STDERR, 'native-str-contains-broken');
	exit(1);
}
if (class_exists('Symfony\\Polyfill\\Php80\\Php80', false)) {
	fwrite(STDERR, 'polyfill-loaded');
	exit(2);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testPhp81PolyfillProvidesArrayIsList(): void
	{
		$result = $this->runAutoloadScript(<<<'PHP'
if (!function_exists('array_is_list')) {
	fwrite(STDERR, 'missing-array-is-list');
	exit(1);
}
if (!array_is_list(['a', 'b'])) {
	fwrite(STDERR, 'array-is-list-wrong');
	exit(2);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testPhp83PolyfillProvidesJsonValidate(): void
	{
		$result = $this->runAutoloadScript(<<<'PHP'
if (!function_exists('json_validate')) {
	fwrite(STDERR, 'missing-json-validate');
	exit(1);
}
if (json_validate('[]') !== true || json_validate('{') !== false) {
	fwrite(STDERR, 'json-validate-wrong');
	exit(2);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testPhp84PolyfillProvidesArrayFind(): void
	{
		$result = $this->runAutoloadScript(<<<'PHP'
if (!function_exists('array_find')) {
	fwrite(STDERR, 'missing-array-find');
	exit(1);
}
$found = array_find([1, 2, 3], static function ($v) {
	return $v === 2;
});
if ($found !== 2) {
	fwrite(STDERR, 'array-find-wrong');
	exit(2);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testPhp85PolyfillProvidesArrayFirstAndLast(): void
	{
		$result = $this->runAutoloadScript(<<<'PHP'
if (!function_exists('array_first') || !function_exists('array_last')) {
	fwrite(STDERR, 'missing-array-first-last');
	exit(1);
}
if (array_first([10, 20]) !== 10 || array_last([10, 20]) !== 20) {
	fwrite(STDERR, 'array-first-last-wrong');
	exit(2);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testMbstringPolyfillClassLoads(): void
	{
		if (!extension_loaded('mbstring')) {
			$this->markTestSkipped('mbstring extension not loaded.');
		}

		$result = $this->runAutoloadScript(<<<'PHP'
if (!class_exists('Symfony\\Polyfill\\Mbstring\\Mbstring', false)) {
	fwrite(STDERR, 'mbstring-polyfill-missing');
	exit(1);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testIntlNormalizerPolyfillClassLoads(): void
	{
		$result = $this->runAutoloadScript(<<<'PHP'
if (!class_exists('Symfony\\Polyfill\\Intl\\Normalizer\\Normalizer', false)) {
	fwrite(STDERR, 'normalizer-polyfill-missing');
	exit(1);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testGraphemePolyfillLoadsWhenIntlIsAbsent(): void
	{
		if (extension_loaded('intl')) {
			$this->markTestSkipped('intl extension is loaded; grapheme polyfill branch is not taken.');
		}

		$result = $this->runAutoloadScript(<<<'PHP'
if (!class_exists('Symfony\\Polyfill\\Intl\\Grapheme\\Grapheme', false)) {
	fwrite(STDERR, 'grapheme-polyfill-missing');
	exit(1);
}
echo 'ok';
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testComposerFlagSkipsPhp84Only(): void
	{
		$result = $this->runAutoloadScript(
			<<<'PHP'

if (function_exists('array_find')) {
	fwrite(STDERR, 'array-find-should-be-absent');
	exit(1);
}
if (class_exists('Symfony\\Polyfill\\Php84\\Php84', false)) {
	fwrite(STDERR, 'php84-class-should-be-absent');
	exit(2);
}
if (!function_exists('array_first') || array_first([7]) !== 7) {
	fwrite(STDERR, 'php85-still-required');
	exit(3);
}
echo 'ok';
PHP
			,
			<<<'PHP'
$GLOBALS['__composer_autoload_files']['9d2b9fc6db0f153a0a149fefb182415e'] = true;
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testComposerFlagSkipsPhp85Only(): void
	{
		$result = $this->runAutoloadScript(
			<<<'PHP'

if (function_exists('array_first')) {
	fwrite(STDERR, 'array-first-should-be-absent');
	exit(1);
}
$found = array_find([1, 2, 3], static function ($v) {
	return $v === 2;
});
if ($found !== 2) {
	fwrite(STDERR, 'array-find-still-required');
	exit(2);
}
echo 'ok';
PHP
			,
			<<<'PHP'
$GLOBALS['__composer_autoload_files']['606a39d89246991a373564698c2d8383'] = true;
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testWrongNamespaceSentinelDoesNotSuppressPhp84(): void
	{
		$result = $this->runAutoloadScript(
			<<<'PHP'

if (!class_exists('Symfony\\Polyfill\\Php84\\Php84', false)) {
	fwrite(STDERR, 'real-php84-missing');
	exit(1);
}
if (!function_exists('array_find')) {
	fwrite(STDERR, 'array-find-missing');
	exit(2);
}
if (array_find([1, 2, 3], static function ($v) {
	return $v === 2;
}) !== 2) {
	fwrite(STDERR, 'array-find-wrong');
	exit(3);
}
echo 'ok';
PHP
			,
			<<<'PHP'
namespace Symfony\Polyfill\Php83;
final class Php84 {}
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testWrongNamespaceSentinelDoesNotSuppressPhp85(): void
	{
		$result = $this->runAutoloadScript(
			<<<'PHP'

if (!class_exists('Symfony\\Polyfill\\Php85\\Php85', false)) {
	fwrite(STDERR, 'real-php85-missing');
	exit(1);
}
if (!function_exists('array_first') || array_first([10]) !== 10) {
	fwrite(STDERR, 'array-first-missing');
	exit(2);
}
echo 'ok';
PHP
			,
			<<<'PHP'
namespace Symfony\Polyfill\Php83;
final class Php85 {}
PHP
		);

		$this->assertSame(0, $result['exitCode']);
	}

	public function testAlreadyLoadedPhp84ClassIsNotRedeclared(): void
	{
		$result = $this->runAutoloadScript(
			<<<'PHP'

if (\Symfony\Polyfill\Php84\Php84::sentinel() !== 'stub') {
	fwrite(STDERR, 'stub-broken');
	exit(1);
}
if (function_exists('array_find')) {
	fwrite(STDERR, 'array-find-should-not-load');
	exit(2);
}
echo 'ok';
PHP
			,
			<<<'PHP'
namespace Symfony\Polyfill\Php84;
final class Php84 {
	public static function sentinel(): string {
		return 'stub';
	}
}
PHP
		);

		$this->assertSame(0, $result['exitCode']);
		$this->assertStringNotContainsString('Cannot declare class', $result['stderr']);
	}

	public function testAlreadyLoadedPhp85ClassIsNotRedeclared(): void
	{
		$result = $this->runAutoloadScript(
			<<<'PHP'

if (\Symfony\Polyfill\Php85\Php85::sentinel() !== 'stub') {
	fwrite(STDERR, 'stub-broken');
	exit(1);
}
if (function_exists('array_first')) {
	fwrite(STDERR, 'array-first-should-not-load');
	exit(2);
}
echo 'ok';
PHP
			,
			<<<'PHP'
namespace Symfony\Polyfill\Php85;
final class Php85 {
	public static function sentinel(): string {
		return 'stub';
	}
}
PHP
		);

		$this->assertSame(0, $result['exitCode']);
		$this->assertStringNotContainsString('Cannot declare class', $result['stderr']);
	}

	public function testAlreadyLoadedPhp80ClassIsNotRedeclared(): void
	{
		if (PHP_VERSION_ID >= 80000) {
			$this->markTestSkipped('PHP 8.0+ provides native str_contains.');
		}

		$result = $this->runAutoloadScript(
			<<<'PHP'

if (\Symfony\Polyfill\Php80\Php80::sentinel() !== 'stub') {
	fwrite(STDERR, 'stub-broken');
	exit(1);
}
if (function_exists('str_contains')) {
	fwrite(STDERR, 'str-contains-should-not-load');
	exit(2);
}
echo 'ok';
PHP
			,
			<<<'PHP'
namespace Symfony\Polyfill\Php80;
final class Php80 {
	public static function sentinel(): string {
		return 'stub';
	}
}
PHP
		);

		$this->assertSame(0, $result['exitCode']);
		$this->assertStringNotContainsString('Cannot declare class', $result['stderr']);
	}

	/**
	 * @return array{exitCode: int, stdout: string, stderr: string}
	 */
	private function runAutoloadScript(string $afterLoadBody, string $beforeBootstrapBody = '', string $beforeLoadClassBody = ''): array
	{
		$repoRoot = dirname(__DIR__);
		$script = '<?php declare(strict_types = 1);' . "\n\n";
		if ($beforeBootstrapBody !== '') {
			$script .= $beforeBootstrapBody . "\n\n";
		}
		$script .= "require '{$repoRoot}/bootstrap.php';\n";
		if ($beforeLoadClassBody !== '') {
			$script .= $beforeLoadClassBody . "\n";
		}
		$script .= "\\PHPStan\\PharAutoloader::loadClass('PHPStan\\ShouldNotHappenException');\n\n";
		$script .= $afterLoadBody;

		return PhpScript::run($script);
	}

}

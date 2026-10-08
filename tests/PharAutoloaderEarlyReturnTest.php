<?php declare(strict_types = 1);

use PHPStan\PharAutoloader;

final class PharAutoloaderEarlyReturnTest extends PharAutoloaderTestCase
{

	public function testForeignClassDoesNotLoadPolyfills(): void
	{
		PharAutoloader::loadClass('Acme\\Widget');

		$this->assertFalse($this->getPolyfillsLoaded());
	}

	public function testPhpDocParserPrefixDoesNotLoadPolyfills(): void
	{
		PharAutoloader::loadClass('PHPStan\\PhpDocParser\\Ast\\Type\\IdentifierTypeNode');

		$this->assertFalse($this->getPolyfillsLoaded());
		$this->assertFalse(class_exists('PHPStan\\PhpDocParser\\Ast\\Type\\IdentifierTypeNode', false));
	}

	public function testMissingPharWrapperThrowsForPhpstanClass(): void
	{
		stream_wrapper_unregister('phar');

		try {
			$this->expectException(\Exception::class);
			$this->expectExceptionMessage('Phar wrapper is not registered. Please review your php.ini settings.');

			PharAutoloader::loadClass('PHPStan\\ShouldNotHappenException');
		} finally {
			$this->ensurePharStreamWrapperRegistered();
		}
	}

	public function testMissingPharWrapperThrowsForInternalPrefix(): void
	{
		stream_wrapper_unregister('phar');

		try {
			$this->expectException(\Exception::class);
			$this->expectExceptionMessage('Phar wrapper is not registered. Please review your php.ini settings.');

			PharAutoloader::loadClass('_PHPStan_f63891026\\Nope');
		} finally {
			$this->ensurePharStreamWrapperRegistered();
		}
	}

	public function testRunningConstantSkipsLoading(): void
	{
		$repoRoot = dirname(__DIR__);
		$script = '<?php declare(strict_types = 1);' . "\n\n";
		$script .= "define('__PHPSTAN_RUNNING__', true);\n";
		$script .= "require '{$repoRoot}/bootstrap.php';\n\n";
		$script .= <<<'PHP'
PHPStan\PharAutoloader::loadClass('PHPStan\ShouldNotHappenException');

if (class_exists('PHPStan\ShouldNotHappenException', false)) {
	fwrite(STDERR, 'loaded');
	exit(1);
}

echo 'not-loaded';
PHP;

		$result = PhpScript::run($script);

		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('not-loaded', trim($result['stdout']));
		$this->assertSame('', $result['stderr']);
	}

}

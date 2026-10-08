<?php declare(strict_types = 1);

use PHPStan\PharAutoloader;
use PHPUnit\Framework\TestCase;

abstract class PharAutoloaderTestCase extends TestCase
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->resetPharAutoloaderState();
		$this->ensurePharStreamWrapperRegistered();
	}

	protected function tearDown(): void
	{
		$this->resetPharAutoloaderState();
		$this->ensurePharStreamWrapperRegistered();
		parent::tearDown();
	}

	protected function resetPharAutoloaderState(): void
	{
		$reflection = new \ReflectionClass(PharAutoloader::class);

		$polyfillsLoaded = $reflection->getProperty('polyfillsLoaded');
		$polyfillsLoaded->setAccessible(true);
		$polyfillsLoaded->setValue(false);

		$composerAutoloader = $reflection->getProperty('composerAutoloader');
		$composerAutoloader->setAccessible(true);
		$composerAutoloader->setValue(null);
	}

	protected function ensurePharStreamWrapperRegistered(): void
	{
		if (!in_array('phar', stream_get_wrappers(), true)) {
			stream_wrapper_restore('phar');
		}
	}

	protected function getPolyfillsLoaded(): bool
	{
		$reflection = new \ReflectionClass(PharAutoloader::class);
		$polyfillsLoaded = $reflection->getProperty('polyfillsLoaded');
		$polyfillsLoaded->setAccessible(true);

		return (bool) $polyfillsLoaded->getValue();
	}

}

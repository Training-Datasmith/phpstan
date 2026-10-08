<?php declare(strict_types = 1);

final class PhpstanBinaryTest extends PharAutoloaderTestCase
{

	public function testPharVersion(): void
	{
		$result = $this->runPhpStan([$this->getPharPath(), '--version']);

		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('', $result['stderr']);
		$this->assertMatchesRegularExpression('/^PHPStan - PHP Static Analysis Tool \d+\.\d+/', trim($result['stdout']));
	}

	public function testShellEntryPointVersion(): void
	{
		$result = $this->runPhpStan([$this->getShellScriptPath(), '--version']);

		$this->assertSame(0, $result['exitCode']);
		$this->assertSame('', $result['stderr']);
		$this->assertMatchesRegularExpression('/^PHPStan - PHP Static Analysis Tool \d+\.\d+/', trim($result['stdout']));
	}

	public function testAnalyseReportsUnknownFunction(): void
	{
		$fixture = $this->writeFixture("<?php\n\nundefined_function_for_phpstan_test();\n");
		$result = $this->runPhpStan([
			$this->getPharPath(),
			'analyse',
			'--no-progress',
			'--error-format=json',
			'--level=0',
			$fixture,
		]);

		$this->assertSame(1, $result['exitCode']);
		$payload = json_decode($result['stdout'], true);
		$this->assertIsArray($payload);
		$this->assertSame(0, $payload['totals']['errors']);
		$this->assertSame(1, $payload['totals']['file_errors']);

		$messages = $this->extractMessages($payload);
		$this->assertCount(1, $messages);
		$this->assertSame('function.notFound', $messages[0]['identifier']);
		$this->assertSame(3, $messages[0]['line']);
	}

	public function testAnalyseReportsArgumentCount(): void
	{
		$fixture = $this->writeFixture(<<<'PHP'
<?php declare(strict_types = 1);

function phpstan_dist_add(int $a, int $b): int
{
	return $a + $b;
}

phpstan_dist_add(1);

PHP
		);

		$result = $this->runPhpStan([
			$this->getPharPath(),
			'analyse',
			'--no-progress',
			'--error-format=json',
			'--level=0',
			$fixture,
		]);

		$this->assertSame(1, $result['exitCode']);
		$payload = json_decode($result['stdout'], true);
		$this->assertIsArray($payload);
		$this->assertSame(0, $payload['totals']['errors']);
		$this->assertSame(1, $payload['totals']['file_errors']);

		$messages = $this->extractMessages($payload);
		$this->assertCount(1, $messages);
		$this->assertSame('arguments.count', $messages[0]['identifier']);
		$this->assertSame(8, $messages[0]['line']);
	}

	public function testAnalyseAcceptsValidFile(): void
	{
		$fixture = $this->writeFixture(<<<'PHP'
<?php declare(strict_types = 1);

function phpstan_dist_ok(): int
{
	return 1;
}

PHP
		);

		$result = $this->runPhpStan([
			$this->getPharPath(),
			'analyse',
			'--no-progress',
			'--error-format=json',
			'--level=0',
			$fixture,
		]);

		$this->assertSame(0, $result['exitCode']);
		$payload = json_decode($result['stdout'], true);
		$this->assertIsArray($payload);
		$this->assertSame(0, $payload['totals']['errors']);
		$this->assertSame(0, $payload['totals']['file_errors']);
	}

	/**
	 * @param list<string> $args
	 * @return array{exitCode: int, stdout: string, stderr: string}
	 */
	private function runPhpStan(array $args): array
	{
		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$cmd = array_merge(
			[PHP_BINARY, '-d', 'memory_limit=512M', '-d', 'display_errors=1', '-d', 'error_reporting=' . (string) E_ALL],
			$args,
		);

		$cwd = sys_get_temp_dir() . '/phpstan-cli-' . bin2hex(random_bytes(8));
		mkdir($cwd);

		$proc = proc_open($cmd, $descriptors, $pipes, $cwd);
		if (!is_resource($proc)) {
			throw new \RuntimeException('proc_open failed');
		}

		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$exitCode = proc_close($proc);

		$this->removeDirectory($cwd);

		return [
			'exitCode' => $exitCode,
			'stdout' => $stdout !== false ? $stdout : '',
			'stderr' => $stderr !== false ? $stderr : '',
		];
	}

	private function getPharPath(): string
	{
		return dirname(__DIR__) . '/phpstan.phar';
	}

	private function getShellScriptPath(): string
	{
		return dirname(__DIR__) . '/phpstan';
	}

	private function writeFixture(string $contents): string
	{
		$path = sys_get_temp_dir() . '/phpstan-fixture-' . bin2hex(random_bytes(8)) . '.php';
		if (file_put_contents($path, $contents) === false) {
			throw new \RuntimeException('Could not write fixture');
		}

		return $path;
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return list<array{identifier: string, line: int}>
	 */
	private function extractMessages(array $payload): array
	{
		$messages = [];
		foreach ($payload['files'] as $fileData) {
			$this->assertIsArray($fileData);
			foreach ($fileData['messages'] as $message) {
				$this->assertIsArray($message);
				$messages[] = [
					'identifier' => $message['identifier'],
					'line' => $message['line'],
				];
			}
		}

		return $messages;
	}

	private function removeDirectory(string $dir): void
	{
		if (!is_dir($dir)) {
			return;
		}

		$items = scandir($dir);
		if ($items === false) {
			return;
		}

		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (is_dir($path)) {
				$this->removeDirectory($path);
			} else {
				@unlink($path);
			}
		}

		@rmdir($dir);
	}

}

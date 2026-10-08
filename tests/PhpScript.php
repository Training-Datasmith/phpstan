<?php declare(strict_types = 1);

final class PhpScript
{

	/**
	 * @return array{exitCode: int, stdout: string, stderr: string}
	 */
	public static function run(string $scriptBody, ?string $cwd = null, int $timeoutSeconds = 30): array
	{
		$tmp = tempnam(sys_get_temp_dir(), 'phpstan-test-');
		if ($tmp === false) {
			throw new \RuntimeException('tempnam failed');
		}

		$path = $tmp . '.php';
		if (!rename($tmp, $path)) {
			throw new \RuntimeException('rename failed');
		}

		if (file_put_contents($path, $scriptBody) === false) {
			throw new \RuntimeException('file_put_contents failed');
		}

		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$cmd = [
			PHP_BINARY,
			'-d', 'display_errors=1',
			'-d', 'error_reporting=' . (string) E_ALL,
			$path,
		];

		$proc = proc_open($cmd, $descriptors, $pipes, $cwd ?? dirname(__DIR__));
		if (!is_resource($proc)) {
			throw new \RuntimeException('proc_open failed');
		}

		fclose($pipes[0]);

		stream_set_blocking($pipes[1], false);
		stream_set_blocking($pipes[2], false);

		$stdout = '';
		$stderr = '';
		$deadline = microtime(true) + $timeoutSeconds;

		while (true) {
			$read = [];
			if (!feof($pipes[1])) {
				$read[] = $pipes[1];
			}
			if (!feof($pipes[2])) {
				$read[] = $pipes[2];
			}

			if ($read === []) {
				break;
			}

			$write = null;
			$except = null;
			$remaining = $deadline - microtime(true);
			if ($remaining <= 0) {
				proc_terminate($proc);
				throw new \RuntimeException('subprocess timed out');
			}

			$sec = (int) floor($remaining);
			$usec = (int) (($remaining - $sec) * 1_000_000);
			$ready = stream_select($read, $write, $except, $sec, $usec);
			if ($ready === false) {
				throw new \RuntimeException('stream_select failed');
			}

			if ($ready === 0) {
				continue;
			}

			foreach ($read as $stream) {
				$chunk = stream_get_contents($stream);
				if ($chunk === false) {
					continue;
				}
				if ($stream === $pipes[1]) {
					$stdout .= $chunk;
				} else {
					$stderr .= $chunk;
				}
			}
		}

		fclose($pipes[1]);
		fclose($pipes[2]);

		$exitCode = proc_close($proc);
		@unlink($path);

		return [
			'exitCode' => $exitCode,
			'stdout' => $stdout,
			'stderr' => $stderr,
		];
	}

}

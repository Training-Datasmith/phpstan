<?php

declare(strict_types=1);

spl_autoload_register(function (): void {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/classes'));
    foreach ($iterator as $file) {
        // ...
    }
});

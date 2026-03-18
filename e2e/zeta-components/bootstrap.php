<?php

declare(strict_types=1);

use App\Autoloader;

$loader = require __DIR__ . '/vendor/autoload.php';

ezcBase::setWorkingDirectory(__DIR__);
ezcBase::addClassRepository(__DIR__);

spl_autoload_register([Autoloader::class, 'autoloadEzc']);

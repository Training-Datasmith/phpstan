<?php

declare(strict_types=1);

namespace PHPUnit10TestTwo;

use function PHPStan\Testing\assertType;

assertType('2', 1 + 1);

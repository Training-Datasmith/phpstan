<?php

declare(strict_types=1);

namespace PHPStan\E2E;

class DynamicCallToStaticMethod
{
    public function foo(): void
    {
        static::bar();
    }

    public static function bar(): void
    {
    }

}

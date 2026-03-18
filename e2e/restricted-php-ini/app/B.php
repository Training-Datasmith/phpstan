<?php

declare(strict_types=1);

class B extends X
{
    public function foo(): void
    {
    }

    public function bar(): void
    {
        var_dump($x);
    }
}

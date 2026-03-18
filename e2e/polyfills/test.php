<?php

declare(strict_types=1);

var_dump(str_contains('test', 't'));

class Foo implements \Stringable
{
    public function __toString(): string
    {
        return 'foo';
    }

}

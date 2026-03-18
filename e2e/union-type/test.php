<?php

declare(strict_types=1);

namespace NativeUnionTypes;

class Foo
{
    public int|bool $fooProp;

    public function doFoo(int|bool $foo): self|Bar
    {
        return new Foo();
    }

}

class Bar
{
}

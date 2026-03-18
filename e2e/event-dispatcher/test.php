<?php

declare(strict_types=1);

namespace TestEventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class Foo implements EventDispatcherInterface
{
    public function doFoo(): void
    {
        $this->dispatch(new \stdClass());
    }

}

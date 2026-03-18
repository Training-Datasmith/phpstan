<?php

declare(strict_types=1);

class defaultActions
{
    public function executePublic(): void
    {
        $this->foo();
    }

    protected function foo(): void
    {
    }
}

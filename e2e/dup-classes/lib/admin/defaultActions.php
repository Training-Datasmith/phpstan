<?php

declare(strict_types=1);

class defaultActions
{
    public function executeAdmin(): void
    {
        $this->bar();
    }

    protected function bar(): void
    {
    }
}

<?php

declare(strict_types=1);

class ThirdpartyClass
{
    public function test(): void
    {
        $this->undefinedVariable = 123;
    }

}

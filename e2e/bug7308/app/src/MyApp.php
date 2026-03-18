<?php

declare(strict_types=1);

class MyApp extends ApplicationController
{
    public function __construct(ExternalClass  $ext)
    {
        $ext->doSomething();
    }
}

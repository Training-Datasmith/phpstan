<?php

declare(strict_types=1);

class MyApp
{
    public function __construct(HttpResponse  $response)
    {
        $response->deleteCookie('foo');
    }
}

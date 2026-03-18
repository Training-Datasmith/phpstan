<?php

declare(strict_types=1);

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class Test extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::createClient();
    }
}

<?php

declare(strict_types=1);

use Tester\Assert;

class TautologyTest extends \Tester\TestCase
{
    public function testOne(): void
    {
        Assert::true(true);
        exit(255);
    }

}

(new TautologyTest())->run();

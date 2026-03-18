<?php

declare(strict_types=1);

namespace App;

class Foo extends \ThirdParty\Bar
{
    public function doFoo(): void
    {
        $this->doBar();
    }

}

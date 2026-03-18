<?php

declare(strict_types=1);

class ExternalClass
{
    public const WARENGRUPPE_STOCKSCHIRME = ClassWithConstants::WARENGRUPPE_STOCKSCHIRME;
    public const WARENGRUPPE_TASCHENSCHIRME = ClassWithConstants::WARENGRUPPE_TASCHENSCHIRME;
    public const WARENGRUPPE_SONNENSCHIRME = ClassWithConstants::WARENGRUPPE_SONNENSCHIRME;

    public function doSomething(): void
    {
        echo "ExternalClass::doSomething()\n";
    }
}

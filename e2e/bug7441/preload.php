<?php

declare(strict_types=1);

eval('class FooOriginal
{
    /**
     * @param bool $saveMode
     */
    public function save($saveMode = false): void
    {
    }
}');

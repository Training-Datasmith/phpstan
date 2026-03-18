<?php

declare(strict_types=1);

namespace App;

class ShouldWork extends \PhpParser\NodeTraverser
{
    protected function traverseArray(array $nodes): array
    {
        return [];
    }
}

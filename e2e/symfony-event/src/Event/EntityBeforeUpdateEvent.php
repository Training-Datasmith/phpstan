<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EntityBeforeUpdateEvent extends Event
{
    public const NAME = 'entity.beforeUpdate';
}

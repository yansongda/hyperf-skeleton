<?php

declare(strict_types=1);

namespace App\Listener\Entity;

use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Model;
use Hyperf\Event\Contract\ListenerInterface;

abstract class AbstractEntityListener implements ListenerInterface
{
    public function isValid(Model $entity): bool
    {
        return false;
    }

    abstract public function listen(): array;

    public function process(object $event): void
    {
        if (!($event instanceof Event) ||
            !$this->isValid($event->getModel()) ||
            !method_exists($this, $event->getMethod())) {
            return;
        }

        $this->{$event->getMethod()}($event->getModel());
    }
}

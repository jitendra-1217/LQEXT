<?php

namespace Jitendra\Lqstuff;

class EventsDispatcher extends Decorated
{
    /**
     * {@inheritDoc}
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        $handler = function () use ($event, $payload, $halt) {
            return $this->dispatcher->dispatch($event, $payload, $halt);
        };
        if ($halt || $this->transactionHandler->shouldBeSync($event)) {
            return $handler();
        }
        $this->transactionHandler->pushPendingHandler($handler);
    }

    /**
     * {@inheritDoc}
     */
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->dispatch($event, $payload, $halt);
    }
}

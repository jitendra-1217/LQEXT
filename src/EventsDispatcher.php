<?php

namespace Jitendra\Lqext;

use Illuminate\Contracts\Events\Dispatcher;

class EventsDispatcher extends Decorated implements Dispatcher
{
    /**
     * {@inheritDoc}
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        $handler = function () use ($event, $payload, $halt) {
            return $this->instance->dispatch($event, $payload, $halt);
        };
        if ($halt || $this->getTransactionHandler()->shouldBeSync($event)) {
            return $handler();
        }
        $this->getTransactionHandler()->pushPendingHandler($handler);
    }

    /**
     * {@inheritDoc}
     */
    public function fire($event, $payload = [], $halt = false)
    {
        return $this->dispatch($event, $payload, $halt);
    }

    /**
     * {@inheritDoc}
     */
    public function listen($events, $listener)
    {
        return $this->instance->listen($events, $listener);
    }

    /**
     * {@inheritDoc}
     */
    public function hasListeners($eventName)
    {
        return $this->instance->hasListeners($eventName);
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe($subscriber)
    {
        return $this->instance->subscribe($subscriber);
    }

    /**
     * {@inheritDoc}
     */
    public function until($event, $payload = [])
    {
        return $this->instance->until($event, $payload);
    }

    /**
     * {@inheritDoc}
     */
    public function push($event, $payload = [])
    {
        return $this->instance->push($event, $payload);
    }

    /**
     * {@inheritDoc}
     */
    public function flush($event)
    {
        return $this->instance->flush($event);
    }

    /**
     * {@inheritDoc}
     */
    public function forget($event)
    {
        return $this->instance->forget($event);
    }

    /**
     * {@inheritDoc}
     */
    public function forgetPushed()
    {
        return $this->instance->forgetPushed();
    }
}

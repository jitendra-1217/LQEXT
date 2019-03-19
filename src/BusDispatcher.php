<?php

namespace Jitendra\Lqext;

use Illuminate\Contracts\Bus\Dispatcher;

class BusDispatcher extends Decorated implements Dispatcher
{
    /**
     * {@inheritDoc}
     */
    public function dispatch($command)
    {
        return $this->getTransactionHandler()->handler(
            $command,
            function () use ($command) {
                return $this->instance->dispatch($command);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchNow($command, $handler = null)
    {
        return $this->instance->dispatchNow($command, $handler);
    }

    /**
     * {@inheritDoc}
     */
    public function pipeThrough(array $pipes)
    {
        return $this->instance->pipeThrough($pipes);
    }
}

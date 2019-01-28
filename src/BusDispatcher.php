<?php

namespace Jitendra\Lqstuff;

class BusDispatcher extends Decorated
{
    /**
     * {@inheritDoc}
     */
    public function dispatch($command)
    {
        return $this->transactionHandler->handler(
            $command,
            function () use ($command) {
                return $this->instance->dispatch($command);
            }
        );
    }
}

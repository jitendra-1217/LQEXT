<?php

namespace Jitendra\Lqstuff;

class Mailer extends Decorated
{
    /**
     * {@inheritDoc}
     */
    public function queue($view, $queue = null)
    {
        return $this->transactionHandler->handler(
            $view,
            function () use ($view, $queue) {
                return $this->instance->queue($view, $queue);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function later($delay, $view, $queue = null)
    {
        return $this->transactionHandler->handler(
            $view,
            function () use ($delay, $view, $queue) {
                return $this->instance->later($delay, $view, $queue);
            }
        );
    }
}

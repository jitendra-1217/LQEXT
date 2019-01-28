<?php

namespace Jitendra\Lqstuff;

class Mailer extends Decorated implements \Illuminate\Contracts\Mail\Mailer
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

    /**
     * {@inheritDoc}
     */
    public function to($users)
    {
        return $this->instance->to($users);
    }

    /**
     * {@inheritDoc}
     */
    public function bcc($users)
    {
        return $this->instance->bcc($users);
    }

    /**
     * {@inheritDoc}
     */
    public function raw($text, $callback)
    {
        return $this->instance->raw($text, $callback);
    }

    /**
     * {@inheritDoc}
     */
    public function send($view, array $data = [], $callback = null)
    {
        return $this->instance->send($view, $data, $callback);
    }

    /**
     * {@inheritDoc}
     */
    public function failures()
    {
        return $this->instance->failures();
    }
}

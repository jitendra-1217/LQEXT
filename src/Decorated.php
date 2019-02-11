<?php

namespace Jitendra\Lqext;

abstract class Decorated
{
    /**
     * @var mixed
     */
    protected $instance;

    /**
     * @var TransactionHandler|null
     */
    protected $transactionHandler;

    public function __construct($instance, TransactionHandler $transactionHandler = null)
    {
        $this->instance = $instance;
        $this->transactionHandler = $transactionHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function __call(string $name, array $arguments)
    {
        $this->instance->$name(...$arguments);
    }
}

<?php

namespace Jitendra\Lqstuff;

abstract class Decorated
{
    /**
     * @var TransactionHandler
     */
    protected $transactionHandler;

    /**
     * @var mixed
     */
    protected $instance;

    /**
     * @param mixed $instance
     */
    public function __construct(TransactionHandler $transactionHandler, mixed $instance)
    {
        $this->transactionHandler = $transactionHandler;
        $this->instance = $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->instance->$name(...$arguments);
    }
}

<?php

namespace Jitendra\Lqext;

/**
 * An object of Decorated class will override few methods of underlying
 * instance and just pass through rest methods.
 */
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

    public function __construct(
        $instance,
        TransactionHandler $transactionHandler = null
    ) {
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

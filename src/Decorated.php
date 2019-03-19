<?php

namespace Jitendra\Lqext;

use Closure;

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
     * @var Closure
     */
    protected $transactionHandlerResolver;

    /**
     * @var TransactionHandler|null
     */
    protected $transactionHandler;

    public function __construct($instance, Closure $transactionHandlerResolver)
    {
        $this->instance = $instance;
        $this->transactionHandlerResolver = $transactionHandlerResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function __call(string $name, array $arguments)
    {
        $this->instance->$name(...$arguments);
    }

    /**
     * @return TransactionHandler|null
     */
    public function getTransactionHandler()
    {
        return $this->transactionHandler ?:
            ($this->transactionHandler = $this->transactionHandlerResolver());
    }
}

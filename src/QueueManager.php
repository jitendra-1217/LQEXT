<?php

namespace Jitendra\Lqext;

use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Queue\Factory;

class QueueManager extends Decorated implements Factory
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(Factory $factory, Storage $storage, LoggerInterface $logger)
    {
        parent::__construct($factory);
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     * Returns decorated queue instance.
     */
    protected function resolve($name)
    {
        return new Queue($this->instance->resolve($name), $this->storage, $this->logger);
    }

    /**
     * {@inheritDoc}
     */
    public function connection($name = null)
    {
        return new Queue($this->instance->connection($name), $this->storage, $this->logger);
    }
}

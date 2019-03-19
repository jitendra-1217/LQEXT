<?php

namespace Jitendra\Lqext;

use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Queue\Factory;

/**
 * @see readme.md Unused class for now.
 */
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

    public function __construct(
        Factory $factory,
        Storage $storage,
        LoggerInterface $logger
    ) {
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
        return new Queue(
            $this->instance->resolve($name),
            $this->storage,
            $this->logger
        );
    }

    /**
     * {@inheritDoc}
     */
    public function connection($name = null)
    {
        return new Queue(
            $this->instance->connection($name),
            $this->storage,
            $this->logger
        );
    }

    /**
     * Retries jobs that failed while pushing.
     * @param int $limit
     */
    public function retryFailedToPushJobs(int $limit = 1000)
    {
        while ($limit-- && ($payloadJson = $this->storage->pop())) {
            list (
                $connectionName,
                $queueName,
                $delay,
                $serializedJob,
                $data) = json_decode($payloadJson, true);
            $job = unserialize($serializedJob);
            $connection = $this->connection($connectionName);
            if ($delay > 0) {
                $connection->laterOn($queueName, $delay, $job, $data);
            } else {
                $connection->pushOn($queueName, $job, $data);
            }
        }
    }
}

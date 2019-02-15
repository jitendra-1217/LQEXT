<?php

namespace Jitendra\Lqext;

use Psr\Log\LoggerInterface;

class Queue extends Decorated implements \Illuminate\Contracts\Queue\Queue
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
        \Illuminate\Contracts\Queue\Queue $queue,
        Storage $storage,
        LoggerInterface $logger
    ) {
        parent::__construct($queue);
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function size($queue = null)
    {
        return $this->instance->size($queue);
    }

    /**
     * {@inheritDoc}
     * Wraps called code inside try..catch and handles exceptions.
     */
    public function push($job, $data = '', $queue = null)
    {
        try {
            $this->logger->debug('Pushing job onto queue');
            return $this->instance->push($job, $data, $queue);
        } catch (\Throwable $e) {
            $this->logger->debug('Job failed to push, logging for later push');
            $this->storage->write(
                uniqid(),
                json_encode(
                    [
                        $this->getConnectionName(),
                        0,
                        serialize(is_object($job) ? clone $job : $job),
                        $data,
                        $queue,
                    ]
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function pushOn($queue, $job, $data = '')
    {
        return $this->instance->pushOn($queue, $job, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->instance->pushRaw($payload, $queue, $options);
    }

    /**
     * {@inheritDoc}
     * Wraps called code inside try..catch and handles exceptions.
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        try {
            $this->logger->debug('Pushing job onto queue');
            return $this->instance->later($delay, $job, $data, $queue);
        } catch (\Throwable $e) {
            $this->logger->debug('Job failed to push, logging for later push');
            $this->storage->write(
                uniqid(),
                json_encode(
                    [
                        $this->getConnectionName(),
                        $delay,
                        serialize(is_object($job) ? clone $job : $job),
                        $data,
                        $queue,
                    ]
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->instance->laterOn($queue, $delay, $job, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        return $this->instance->bulk($jobs, $data, $queue);
    }

    /**
     * {@inheritDoc}
     */
    public function pop($queue = null)
    {
        return $this->instance->pop($queue);
    }

    /**
     * {@inheritDoc}
     */
    public function getConnectionName()
    {
        return $this->instance->getConnectionName();
    }

    /**
     * {@inheritDoc}
     */
    public function setConnectionName($name)
    {
        return $this->instance->setConnectionName($name);
    }
}

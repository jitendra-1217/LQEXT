<?php

namespace Jitendra\Lqext;

use Psr\Log\LoggerInterface;

/**
 * @see readme.md Unused class for now.
 */
class RedisStorage implements Storage
{
    const FAILED_JOBS_LIST_NAME = 'lqext:failed_jobs';

    /**
     * @var \Illuminate\Redis\Connections\Connection
     */
    protected $redis;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct($redis, LoggerInterface $logger)
    {
        $this->redis = $redis;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function push(string $data)
    {
        try {
            $this->redis->rpush(self::FAILED_JOBS_LIST_NAME, $data);
        } catch (\Throwable $e) {
            $this->logger->error($e, compact($data));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function pop()
    {
        return $this->redis->lpop(self::FAILED_JOBS_LIST_NAME);
    }
}

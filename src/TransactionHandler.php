<?php

namespace Jitendra\Lqext;

use Closure;
use Psr\Log\LoggerInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class TransactionHandler
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * Stack of transactions(their connection name) in progress.
     * @var array
     */
    protected $transactions;

    /**
     * Map of transaction sequence/level and pending handlers withing them.
     * @var array
     */
    protected $pendingHandlers;

    /**
     * Count of the number of transaction skipped
     * @var int
     */
    protected $testingTxnSkipCount = 0;

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger,
        array $config
    ) {
        $this->setTransactionListeners($dispatcher);
        $this->logger = $logger;
        $this->config = $config;
        $this->transactions = [];
        $this->pendingHandlers = [];
    }

    /**
     * @param  mixed   $command
     * @param  Closure $callback
     * @return mixed
     */
    public function handler($command, Closure $callback)
    {
        if ($this->shouldBeSync($command)) {
            return $callback();
        } else {
            $this->pushPendingHandler($callback);
        }
    }

    /**
     * @param  Closure $callback
     * @return void
     */
    public function pushPendingHandler(Closure $callback)
    {
        $this->pendingHandlers[count($this->transactions)][] = $callback;
        $this->logger->debug('Pending handler pushed');
    }

    /**
     * Returns true if the event, command or mailer should be just dispatched
     * immediately, false otherwise.
     * @param  mixed $object
     * @return boolean
     */
    public function shouldBeSync($object): bool
    {
        if ($this->isTransactionActive()) {
            $isTransactionAware = is_object($object) &&
                in_array(TransactionAware::class, class_uses_recursive($object));
            $isWhitelisted = in_array(
                is_object($object) ? get_class($object) : $object,
                $this->config['transaction']['whitelist']
            );
            return ! $isTransactionAware && ! $isWhitelisted;
        } else {
            return true;
        }
    }

    /**
     * @return boolean
     */
    public function isTransactionActive(): bool
    {
        return count($this->transactions) > 0;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    protected function setTransactionListeners(Dispatcher $dispatcher)
    {
        $dispatcher->listen(
            TransactionBeginning::class,
            function (TransactionBeginning $event) {
                $this->transactionBeginning($event->connection);
            }
        );
        $dispatcher->listen(
            TransactionCommitted::class,
            function (TransactionCommitted $event) {
                $this->transactionCommitted($event->connection);
            }
        );
        $dispatcher->listen(
            TransactionRolledBack::class,
            function (TransactionRolledBack $event) {
                $this->transactionRolledBack($event->connection);
            }
        );
    }

    protected function transactionBeginning(Connection $connection)
    {
        if (($this->config['transaction']['testing'] === true) and
            ($this->testingTxnSkipCount < $this->config['transaction']['testing_txn_skip_count']))
        {
            $this->logger->debug('Testing transaction skipped');
            $this->testingTxnSkipCount++;
            return;
        }
        array_unshift($this->transactions, $connection->getName());
        $this->logger->debug('New transaction begins');
    }

    protected function transactionCommitted(Connection $connection)
    {
        $pendingHandlers = $this->pendingHandlers[count($this->transactions)] ?? [];
        array_shift($this->transactions);
        // If a wrapping transaction exists with same connection name at a level
        // above, merge pending handlers of this level with that one.
        // Else invoke this level handlers.
        if (($level = array_search($connection->getName(), $this->transactions))) {
            $level++;
            $this->pendingHandlers[$level] = array_merge($this->pendingHandlers[$level] ?? [], $pendingHandlers);
            $this->logger->debug('Pending handlers moved to wrapping transaction on same connection');
        } else {
            foreach ($pendingHandlers as $handler) {
                $handler();
                $this->logger->debug('Pending handler executed');
            }
        }
        $this->logger->debug('Transaction committed');
    }

    protected function transactionRolledBack(Connection $connection)
    {
        unset($this->pendingHandlers[count($this->transactions)]);
        array_shift($this->transactions);
        $this->logger->debug('Transaction rolled back');
    }
}

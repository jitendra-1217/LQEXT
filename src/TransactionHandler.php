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

        $debugData = [];

        if ($this->isDebugMode() === true) {
            $debugData = [
                'trace'        => $this->getLimitedStackTrace(),
                'connection'   => $this->transactions[0],
                'pushed_level' => count($this->transactions),
            ];
        }

        $this->logger->debug('Pending handler pushed', $debugData);
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
        if (env('APP_ENV') === 'testing' &&
            $this->testingTxnSkipCount < $this->config['transaction']['testing_txn_skip_count']) {
            $this->logger->debug('Testing transaction skipped');
            $this->testingTxnSkipCount++;
            return;
        }
        array_unshift($this->transactions, $connection->getName());

        $debugData = [];

        if ($this->isDebugMode() === true) {
            $debugData = [
                'connection'    => $connection->getName(),
                'current_level' => count($this->transactions),
            ];
        }

        $this->logger->debug('New transaction begins', $debugData);
    }

    protected function transactionCommitted(Connection $connection)
    {
        $pendingHandlers = $this->pendingHandlers[count($this->transactions)] ?? [];
        unset($this->pendingHandlers[count($this->transactions)]);

        $debugData = [];

        if ($this->isDebugMode() === true) {
            $debugData = [
                'connection'                     => $connection->getName(),
                'level_committed'                => count($this->transactions),
                'pending_handlers_current_level' => count($pendingHandlers),
            ];
        }

        array_shift($this->transactions);
        // If a wrapping transaction exists with same connection name at a level
        // above, merge pending handlers of this level with that one.
        // Else invoke this level handlers.
        if (($level = array_search($connection->getName(), $this->transactions)) and ($level !== false)) {
            $level = count($this->transactions) - $level;
            $this->pendingHandlers[$level] = array_merge($this->pendingHandlers[$level] ?? [], $pendingHandlers);

            if ($this->isDebugMode() === true) {
                $debugData['new_level_after_shifting']   = $level;
                $debugData['pending_handlers_new_level'] = count($this->pendingHandlers[$level]);
            }

            $this->logger->debug('Pending handlers moved to wrapping transaction on same connection', $debugData);
        } else {
            foreach ($pendingHandlers as $handler) {
                $handler();
                $this->logger->debug('Pending handler executed', $debugData);
            }
        }

        $this->logger->debug('Transaction committed', $debugData);
    }

    protected function transactionRolledBack(Connection $connection)
    {
        $debugData = [];

        if ($this->isDebugMode() === true) {
            $debugData = [
                'connection'    => $connection->getName(),
                'current_level' => count($this->transactions),
            ];
        }

        unset($this->pendingHandlers[count($this->transactions)]);
        array_shift($this->transactions);

        $this->logger->debug('Transaction rolled back', $debugData);
    }

    protected function getLimitedStackTrace()
    {
        $backTrace = debug_backtrace();
        $traceData = [];
        foreach ($backTrace as $trace) {
            if (isset($trace['class']) === true) {
                $function = $trace['class']. $trace['type']. $trace['function'];
            } else {
                $function = $trace['function'];
            }

            $line = $trace['line'] ?? 'unknown';
            $traceData[] = $function. '---'. $line;
        }
        return $traceData;
    }

    protected function isDebugMode()
    {
        return $this->config['transaction']['debug'] === true;
    }
}

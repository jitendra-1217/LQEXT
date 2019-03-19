<?php

namespace Jitendra\Lqext;

use Illuminate\Support\Facades\Redis;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'lqext');
        $config = $this->app->config->get('lqext');
        if ($config['transaction']['enable']) {
            $this->app->afterResolving(
                'events',
                function (\Illuminate\Contracts\Events\Dispatcher $dispatcher) use ($config) {
                    $this->app->singleton(
                        TransactionHandler::class,
                        function () use ($config, $dispatcher) {
                            return new TransactionHandler(
                                $dispatcher,
                                $this->app->log,
                                $config
                            );
                        }
                    );
                }
            );
            // Following 3 Laravel's services are extended to provide package's
            // said extensibility. These services are responsible for events
            // dispatching, queued job dispatching and queued mailers.
            $this->app->extend(
                'events',
                function (\Illuminate\Contracts\Events\Dispatcher $dispatcher) {
                    return new EventsDispatcher(
                        $dispatcher,
                        function () {
                            return $this->app->make(TransactionHandler::class);
                        }
                    );
                }
            );
            $this->app->extend(
                \Illuminate\Bus\Dispatcher::class,
                function (\Illuminate\Bus\Dispatcher $dispatcher) {
                    return new BusDispatcher(
                        $dispatcher,
                        function () {
                            return $this->app->make(TransactionHandler::class);
                        }
                    );
                }
            );
            $this->app->extend(
                'mailer',
                function (\Illuminate\Contracts\Mail\Mailer $mailer) {
                    return new Mailer(
                        $mailer,
                        function () {
                            return $this->app->make(TransactionHandler::class);
                        }
                    );
                }
            );
        }
        /**
         * @see readme.md
         */
        // if ($config['queue']['enable']) {
        //     $this->app->extend(
        //         'queue',
        //         function (\Illuminate\Contracts\Queue\Factory $factory) use ($config) {
        //             return new QueueManager(
        //                 $factory,
        //                 new RedisStorage(
        //                     Redis::connection($config['queue']['redis']['connection']),
        //                     $this->app->log,
        //                 ),
        //                 $this->app->log
        //             );
        //         }
        //     );
        // }
    }

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/config.php' => $this->app->basePath().'/config/lqext.php',
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function provides()
    {
        return [
            TransactionHandler::class,
        ];
    }
}

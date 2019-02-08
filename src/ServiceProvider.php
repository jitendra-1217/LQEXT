<?php

namespace Jitendra\Lqstuff;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'lqstuff');
        $config = $this->app->config->get('lqstuff');
        if (! $config['enable']) {
            return;
        }
        $this->app->singleton(
            TransactionHandler::class,
            function () use ($config) {
                return new TransactionHandler($this->app->events, $this->app->log, $config);
            }
        );
        // Following 3 Laravel's services are extended to provide package's
        // said extensibility. These services are responsible for events
        // dispatching, queued job dispatching and queued mailers.
        $this->app->extend(
            'events',
            function (\Illuminate\Contracts\Events\Dispatcher $dispatcher) {
                return new EventsDispatcher($this->app->make(TransactionHandler::class), $dispatcher);
            }
        );
        $this->app->extend(
            \Illuminate\Bus\Dispatcher::class,
            function (\Illuminate\Bus\Dispatcher $dispatcher) {
                return new BusDispatcher($this->app->make(TransactionHandler::class), $dispatcher);
            }
        );
        $this->app->extend(
            'mailer',
            function (\Illuminate\Contracts\Mail\Mailer $mailer) {
                return new Mailer($this->app->make(TransactionHandler::class), $mailer);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/config.php' => $this->app->basePath().'/config/lqstuff.php',
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

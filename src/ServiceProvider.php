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
                return new TransactionHandler($this->app->events, $config);
            }
        );
        // Following 3 Laravel's services are extended to provide package's
        // said extensibility. These services are responsible for events
        // dispatching, queued job dispatching and queued mailers.
        $this->app->extend(
            'events',
            function (\Illuminate\Events\Dispatcher $dispatcher) {
                return EventsDispatcher($this->app->make(TransactionHandler::class), $dispatcher);
            }
        );
        $this->app->extends(
            Illuminate\Contracts\Bus\Dispatcher::class,
            function (\Illuminate\Bus\Dispatcher $dispatcher) {
                return BusDispatcher($this->app->make(TransactionHandler::class), $dispatcher);
            }
        );
        $this->app->extends(
            'mailer',
            function (\Illuminate\Mail\Mailer $mailer) {
                return Mailer($this->app->make(TransactionHandler::class), $mailer);
            }
        );
    }
}

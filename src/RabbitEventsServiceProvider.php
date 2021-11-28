<?php

namespace Ecavalier\Events;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Ecavalier\Events\Amqp\Connection;
use Ecavalier\Events\Amqp\TopicFactory;
use Ecavalier\Events\Console\EventsListCommand;
use Ecavalier\Events\Console\InstallCommand;
use Ecavalier\Events\Console\ListenCommand;
use Ecavalier\Events\Console\ObserverMakeCommand;
use Ecavalier\Events\Facades\RabbitEvents;
use Ecavalier\Events\Queue\Context;

class RabbitEventsServiceProvider extends ServiceProvider
{
    public const DEFAULT_EXCHANGE_NAME = 'events';

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ListenCommand::class,
            InstallCommand::class,
            EventsListCommand::class,
            ObserverMakeCommand::class,
        ]);

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                RabbitEvents::listen($event, $listener);
            }
        }
    }

    public function register(): void
    {
        $config = $this->resolveConfig();

        $this->offerPublishing();

        $this->app->singleton(
            Context::class,
            function () use ($config) {
                return (new Connection($config))->createContext();
            }
        );
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    protected function resolveConfig(): array
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/rabbitevents.php',
            'rabbitevents'
        );

        $config = $this->app['config']['rabbitevents'];

        $defaultConnection = Arr::get($config, 'default');

        return Arr::get($config, "connections.$defaultConnection", []);
    }

    /**
     * Setup the resource publishing groups for RabbitEvents.
     *
     * @return void
     */
    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $providerName = 'RabbitEventsServiceProvider';

            $this->publishes([
                __DIR__ . "/../stubs/{$providerName}.stub" => $this->app->path("Providers/{$providerName}.php"),
            ], 'rabbitevents-provider');
            $this->publishes([
                __DIR__ . '/../config/rabbitevents.php' => $this->app->configPath('rabbitevents.php'),
            ], 'rabbitevents-config');
        }
    }
}

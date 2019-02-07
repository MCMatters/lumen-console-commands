<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use McMatters\LumenConsoleCommands\Console\Commands\{
    Application\DownCommand, Application\KeyGenerateCommand,
    Application\StorageLinkCommand, Application\UpCommand,
    Route\ListCommand as RouteListCommand, View\CacheCommand as ViewCacheCommand,
    View\ClearCommand as ViewClearCommand
};
use McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager;

/**
 * Class ServiceProvider
 *
 * @package McMatters\LumenConsoleCommands
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->app->configure('console-commands');
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->registerApplicationCommands();
        $this->registerRouteCommands();
        $this->registerViewCommands();
        $this->registerCommands();
    }

    /**
     * @return void
     */
    protected function registerApplicationCommands()
    {
        $this->app->singleton(
            'command.lumen-console-commands.key-generate',
            function ($app) {
                return new KeyGenerateCommand($app);
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.storage-link',
            function ($app) {
                return new StorageLinkCommand($app);
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.down',
            function ($app) {
                return new DownCommand(new MaintenanceModeManager($app));
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.up',
            function ($app) {
                return new UpCommand(new MaintenanceModeManager($app));
            }
        );
    }

    /**
     * @return void
     */
    protected function registerRouteCommands()
    {
        $this->app->singleton(
            'command.lumen-console-commands.route-list',
            function ($app) {
                return new RouteListCommand($app);
            }
        );
    }

    /**
     * @return void
     */
    protected function registerViewCommands()
    {
        $this->app->singleton(
            'command.lumen-console-commands.view-cache',
            function ($app) {
                return new ViewCacheCommand($app);
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.view-clear',
            function ($app) {
                return new ViewClearCommand($app);
            }
        );
    }

    /**
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DownCommand::class,
                KeyGenerateCommand::class,
                StorageLinkCommand::class,
                UpCommand::class,
                RouteListCommand::class,
                ViewCacheCommand::class,
                ViewClearCommand::class,
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use McMatters\LumenConsoleCommands\Console\Commands\Application\DownCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Application\KeyGenerateCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Application\StorageLinkCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Application\UpCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Config\CacheCommand as ConfigCacheCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Config\ClearCommand as ConfigClearCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Route\CacheCommand as RouteCacheCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Route\ClearCommand as RouteClearCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Route\ListCommand as RouteListCommand;
use McMatters\LumenConsoleCommands\Console\Commands\Vendor\PublishCommand;
use McMatters\LumenConsoleCommands\Console\Commands\View\CacheCommand as ViewCacheCommand;
use McMatters\LumenConsoleCommands\Console\Commands\View\ClearCommand as ViewClearCommand;
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
        $this->registerConfigCommands();
        $this->registerRouteCommands();
        $this->registerVendorCommands();
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
    protected function registerConfigCommands()
    {
        $this->app->singleton(
            'command.lumen-console-commands.config-cache',
            function ($app) {
                return new ConfigCacheCommand($app, $app->make('files'));
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.config-clear',
            function ($app) {
                return new ConfigClearCommand($app, $app->make('files'));
            }
        );
    }

    /**
     * @return void
     */
    protected function registerRouteCommands()
    {
        $this->app->singleton(
            'command.lumen-console-commands.route-cache',
            function ($app) {
                return new RouteCacheCommand($app);
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.route-clear',
            function ($app) {
                return new RouteClearCommand($app);
            }
        );

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
    protected function registerVendorCommands()
    {
        $this->app->singleton(
            'command.lumen-console-commands.vendor-publish',
            function ($app) {
                return new PublishCommand($app->make('files'));
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
                ConfigCacheCommand::class,
                ConfigClearCommand::class,
                RouteCacheCommand::class,
                RouteClearCommand::class,
                RouteListCommand::class,
                PublishCommand::class,
                ViewCacheCommand::class,
                ViewClearCommand::class,
            ]);
        }
    }
}

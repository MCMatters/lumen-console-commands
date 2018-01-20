<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use McMatters\LumenConsoleCommands\Console\Commands\{
    Application\KeyGenerateCommand, Application\StorageLinkCommand,
    Route\ListCommand as RouteListCommand, View\ClearCommand as ViewClearCommand
};
use const false;
use function is_dir, mkdir;

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
        if (($configPath = $this->getConfigPath()) === false) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/console-commands.php' => "{$configPath}/console-commands.php",
        ]);
    }

    /**
     * @return void
     */
    public function register()
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
            'command.lumen-console-commands.view-clear',
            function ($app) {
                return new ViewClearCommand($app);
            }
        );

        $this->app->singleton(
            'command.lumen-console-commands.route-list',
            function ($app) {
                return new RouteListCommand($app);
            }
        );

        $this->commands([
            'command.lumen-console-commands.key-generate',
            'command.lumen-console-commands.storage-link',
            'command.lumen-console-commands.view-clear',
            'command.lumen-console-commands.route-list',
        ]);
    }

    /**
     * @return bool|string
     */
    protected function getConfigPath()
    {
        $path = $this->app->basePath().DIRECTORY_SEPARATOR.'config';

        if (!is_dir($path) && !@mkdir($path, 0755)) {
            return false;
        }

        return $path;
    }
}

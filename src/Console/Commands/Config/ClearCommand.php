<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Config;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Laravel\Lumen\Application;

/**
 * Class ClearCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Config
 */
class ClearCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'config:clear';

    /**
     * @var string
     */
    protected $description = 'Remove the configuration cache file';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * ClearCommand constructor.
     *
     * @param \Laravel\Lumen\Application $app
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Application $app, Filesystem $files)
    {
        parent::__construct();

        $this->app = $app;
        $this->files = $files;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->files->delete(
            $this->app->make('config')->get('console-commands.cache.config')
        );

        $this->info('Configuration cache cleared!');
    }
}

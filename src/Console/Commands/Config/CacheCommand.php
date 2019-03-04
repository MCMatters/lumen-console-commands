<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Config;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Laravel\Lumen\Application;
use LogicException;
use Throwable;
use const PHP_EOL;
use const true;
use function var_export;

/**
 * Class CacheCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Config
 */
class CacheCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'config:cache';

    /**
     * @var string
     */
    protected $description = 'Create a cache file for faster configuration loading';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * CacheCommand constructor.
     *
     * @param \Laravel\Lumen\Application $app
     * @param \Illuminate\Contracts\Filesystem\Filesystem $files
     */
    public function __construct(Application $app, Filesystem $files)
    {
        parent::__construct();

        $this->app = $app;
        $this->files = $files;
    }

    /**
     * @return void
     * @throws \LogicException
     */
    public function handle()
    {
        $this->call('config:clear');

        $config = $this->app->make('config')->all();
        $configPath = Arr::get($config, 'console-commands.cache.config');

        $this->files->put(
            $configPath,
            '<?php return '.var_export($config, true).';'.PHP_EOL
        );

        try {
            require $configPath;
        } catch (Throwable $e) {
            $this->files->delete($configPath);

            throw new LogicException('Your configuration files are not serializable.', 0, $e);
        }

        $this->info('Configuration cached successfully!');
    }
}

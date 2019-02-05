<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Application;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use function file_exists;

/**
 * Class StorageLinkCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Application
 */
class StorageLinkCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'storage:link';

    /**
     * @var string
     */
    protected $description = 'Create a symbolic link from "public/storage" to "storage/app/public"';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * StorageLinkCommand constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->app = $app;
        $this->files = $app->make('files');
    }

    /**
     * @return void
     */
    public function handle()
    {
        $publicPath = $this->app->basePath('public/storage');

        if (file_exists($publicPath)) {
            $this->error('The "public/storage" directory already exists.');

            return;
        }

        $this->files->link($this->app->storagePath('app/public'), $publicPath);

        $this->info('The [public/storage] directory has been linked.');
    }
}

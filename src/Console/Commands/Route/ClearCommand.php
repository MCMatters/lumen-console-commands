<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Route;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;

use function file_exists, unlink;

/**
 * Class ClearCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Route
 */
class ClearCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'route:clear';

    /**
     * @var string
     */
    protected $description = 'Remove the route cache file';

    /**
     * @var string
     */
    protected $path;

    /**
     * ClearCommand constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->path = $app->make('config')->get('console-commands.cache.routes');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        if (!file_exists($this->path) || @unlink($this->path)) {
            $this->info('Route cache cleared.');
        } else {
            $this->warn('Can\'t delete cache file.');
        }
    }
}

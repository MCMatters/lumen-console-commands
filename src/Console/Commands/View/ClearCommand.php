<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Console\Commands\View;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;

use function glob;
use function is_file;
use function preg_quote;
use function unlink;

/**
 * Class ClearCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\View
 */
class ClearCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'view:clear';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * Clear constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->app = $app;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $path = $this->app->storagePath('framework/views');

        foreach (glob(preg_quote($path, '/').'\/*.php') as $file) {
            if (!is_file($file)) {
                continue;
            }

            @unlink($file);
        }

        $this->info('Compiled views cleared!');
    }
}

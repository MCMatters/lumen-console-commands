<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\View;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use function glob, is_file, preg_quote, unlink;

/**
 * Class Clear
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\View
 */
class Clear extends Command
{
    /**
     * @var string
     */
    protected $signature = 'view:clear';

    /**
     * @var Application
     */
    protected $app;

    /**
     * Clear constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
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

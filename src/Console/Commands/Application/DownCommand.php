<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Application;

use Illuminate\Console\Command;
use McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager;

/**
 * Class Down
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Application
 */
class DownCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'down';

    /**
     * @var \McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager
     */
    protected $manager;

    /**
     * DownCommand constructor.
     *
     * @param \McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager $manager
     */
    public function __construct(MaintenanceModeManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function handle(): void
    {
        if ($this->manager->isDown()) {
            $this->info('Application is already in maintenance mode');
        } else {
            $this->manager->down();
            $this->warn('Application is now in maintenance mode');
        }
    }
}

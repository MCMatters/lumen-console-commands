<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Application;

use Illuminate\Console\Command;
use McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager;

/**
 * Class UpCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Application
 */
class UpCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'up';

    /**
     * @var \McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager
     */
    protected $manager;

    /**
     * UpCommand constructor.
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
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function handle()
    {
        if ($this->manager->isUp()) {
            $this->info('Application is already live');
        } else {
            $this->manager->up();
            $this->info('Application is now live');
        }
    }
}

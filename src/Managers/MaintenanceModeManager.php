<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Managers;

use Laravel\Lumen\Application;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use function file_exists, touch, unlink;

/**
 * Class MaintenanceModeManager
 *
 * @package McMatters\LumenConsoleCommands\Managers
 */
class MaintenanceModeManager
{
    /**
     * @var string
     */
    protected $file;

    /**
     * MaintenanceModeManager constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        $this->file = $app->storagePath('framework/down');
    }

    /**
     * @return bool
     */
    public function isDown(): bool
    {
        return file_exists($this->file);
    }

    /**
     * @return bool
     */
    public function isUp(): bool
    {
        return !$this->isDown();
    }

    /**
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function down(): void
    {
        if ($this->isDown()) {
            return;
        }

        if (!touch($this->file)) {
            throw new FileException(
                'Something went wrong on trying to create maintenance file'
            );
        }
    }

    /**
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function up(): void
    {
        if ($this->isUp()) {
            return;
        }

        if (!unlink($this->file)) {
            throw new FileException(
                'Something went wrong on trying to remove maintenance file'
            );
        }
    }
}

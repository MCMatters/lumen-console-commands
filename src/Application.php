<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands;

use Laravel\Lumen\Application as BaseApplication;
use McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager;
use const false, true;
use function file_exists;

/**
 * Class Application
 *
 * @package McMatters\LumenConsoleCommands
 */
class Application extends BaseApplication
{
    /**
     * @return bool
     */
    public function isDownForMaintenance(): bool
    {
        return (new MaintenanceModeManager($this))->isDown();
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        // Important for config files. Don't remove this row below.
        $app = $this;

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * @return string
     */
    public function getCachedConfigPath(): string
    {
        return $_ENV['APP_CONFIG_CACHE'] ??
            $this->make('config')->get('console-commands.cache.config');
    }

    /**
     * @return bool
     */
    public function configurationIsCached(): bool
    {
        return file_exists($this->getCachedConfigPath());
    }

    /**
     * @return bool
     */
    public function loadCachedConfiguration(): bool
    {
        if ($this->configurationIsCached()) {
            $configuration = require $this->getCachedConfigPath();

            foreach ($configuration as $key => $data) {
                $this->loadedConfigurations[$key] = true;
            }

            $this->make('config')->set($configuration);

            return true;
        }

        return false;
    }
}

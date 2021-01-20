<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use McMatters\LumenConsoleCommands\Exceptions\MaintenanceModeException;
use McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager;

/**
 * Class CheckForMaintenanceMode
 *
 * @package McMatters\LumenConsoleCommands\Http\Middleware
 */
class CheckForMaintenanceMode
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param \McMatters\LumenConsoleCommands\Managers\MaintenanceModeManager $manager
     *
     * @return mixed
     *
     * @throws \McMatters\LumenConsoleCommands\Exceptions\MaintenanceModeException
     */
    public function handle(
        Request $request,
        Closure $next,
        MaintenanceModeManager $manager
    ) {
        if ($manager->isDown()) {
            throw new MaintenanceModeException();
        }

        return $next($request);
    }
}

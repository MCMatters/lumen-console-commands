<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Exceptions;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use const null;

/**
 * Class MaintenanceModeException
 *
 * @package McMatters\LumenConsoleCommands\Exceptions
 */
class MaintenanceModeException extends ServiceUnavailableHttpException
{
    /**
     * MaintenanceModeException constructor.
     */
    public function __construct()
    {
        parent::__construct(null, 'Application is in maintenance mode');
    }
}

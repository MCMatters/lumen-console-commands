<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Route;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use McMatters\LumenHelpers\RouteHelper;
use Symfony\Component\Console\Input\InputOption;

use function array_filter, array_map, implode, is_array;

use const null;

/**
 * Class ListCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Route
 */
class ListCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'route:list';

    /**
     * @var string
     */
    protected $description = 'List of registered routes';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * ListCommand constructor.
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
        $this->table($this->headers, $this->getRoutes());
    }

    /**
     * @return array
     */
    protected function getRoutes(): array
    {
        $routes = RouteHelper::getRoutes(
            array_filter([
                'method' => $this->option('method'),
                'name' => $this->option('name'),
                'path' => $this->option('path'),
            ]),
            'Closure'
        );

        return array_map(function (array $route) {
            if (is_array($route['middleware'])) {
                $route['middleware'] = implode(',', $route['middleware']);
            }

            return $route;
        }, $routes);
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method.'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name.'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path.'],
        ];
    }
}

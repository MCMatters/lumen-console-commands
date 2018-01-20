<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Route;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Lumen\Application;
use Symfony\Component\Console\Input\InputOption;
use const null;
use function implode;

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
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * ListCommand constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->app = $app;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->table($this->headers, $this->getRoutes());
    }

    /**
     * @return array
     */
    protected function getRoutes(): array
    {
        $routes = [];

        $filterMethod = $this->option('method');
        $filterName = $this->option('name');
        $filterPath = $this->option('path');

        foreach ($this->app->router->getRoutes() as $route) {
            if ((null !== $filterMethod && Arr::get($route, 'method') !== $filterMethod) ||
                (null !== $filterName && !Str::contains(Arr::get($route, 'action.as', ''), $filterName)) ||
                (null !== $filterPath && !Str::contains(Arr::get($route, 'uri', ''), $filterPath))
            ) {
                continue;
            }

            $routes[] = $this->getRouteInformation($route);
        }

        return $routes;
    }

    /**
     * @param array $route
     *
     * @return array
     */
    protected function getRouteInformation(array $route): array
    {
        return [
            'method'     => Arr::get($route, 'method'),
            'uri'        => Arr::get($route, 'uri'),
            'name'       => Arr::get($route, 'action.as'),
            'action'     => $this->getRouteAction($route),
            'middleware' => $this->getRouteMiddlewares($route),
        ];
    }

    /**
     * @param array $route
     *
     * @return string
     */
    protected function getRouteAction(array $route): string
    {
        $action = Arr::get($route, 'action.uses');

        return $action instanceof Closure ? 'Closure' : $action;
    }

    /**
     * @param array $route
     *
     * @return string
     */
    protected function getRouteMiddlewares(array $route): string
    {
        $middleware = Arr::get($route, 'action.middleware', []);

        if ($middleware instanceof Closure) {
            return 'Closure';
        }

        $middlewares = [];

        foreach ((array) $middleware as $item) {
            if ($middleware instanceof Closure) {
                $middlewares[] = 'Closure';
            } else {
                $middlewares[] = $item;
            }
        }

        return implode(',', $middlewares);
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

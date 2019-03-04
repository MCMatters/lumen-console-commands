<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Route;

use Closure;
use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use LogicException;

/**
 * Class CacheCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Route
 */
class CacheCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'route:cache';

    /**
     * @var string
     */
    protected $description = 'Create a route cache file for faster route registration';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * Cache constructor.
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
     * @throws \LogicException
     */
    public function handle()
    {
        $this->call('route:clear');

        file_put_contents(
            $this->app->make('config')->get('console-commands.cache.routes'),
            $this->getContent()
        );

        $this->info('Routes cached successfully.');
    }

    /**
     * @return string
     * @throws \LogicException
     */
    protected function getContent(): string
    {
        $routes = $this->getRoutes();

        $content = file_get_contents(__DIR__.'/../../stubs/routes/cache.stub');

        return str_replace('{{routes}}', var_export($routes, true), $content);
    }

    /**
     * @return array
     * @throws \LogicException
     */
    protected function getRoutes(): array
    {
        $app = require $this->app->basePath().'/bootstrap/app.php';

        $routes = $app->router->getRoutes();

        $this->checkRoutes($routes);

        return $routes;
    }

    /**
     * @param array $routes
     *
     * @return void
     * @throws \LogicException
     */
    protected function checkRoutes(array $routes = [])
    {
        foreach ($routes as $route) {
            if ((!empty($route['action']['uses']) && $route['action']['uses'] instanceof Closure) ||
                (!empty($route['action'][0]) && $route['action'][0] instanceof Closure)
            ) {
                throw new LogicException("Unable to cache route [{$route['uri']}]");
            }
        }
    }
}

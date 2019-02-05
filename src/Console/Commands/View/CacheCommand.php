<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\View;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravel\Lumen\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class CacheCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\View
 */
class CacheCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'view:cache';

    /**
     * @var string
     */
    protected $description = "Compile all of the application's Blade templates";

    /**
     * @var \Illuminate\View\Factory
     */
    protected $view;

    /**
     * CacheCommand constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->view = $app->make('view');
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->paths()->each(function ($path) {
            $this->compileViews($this->bladeFilesIn([$path]));
        });

        $this->info('Blade templates cached successfully!');
    }

    /**
     * @param \Illuminate\Support\Collection $views
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function compileViews(Collection $views)
    {
        /** @var \Illuminate\View\Compilers\BladeCompiler $compiler */
        $compiler = $this->view
            ->getEngineResolver()
            ->resolve('blade')
            ->getCompiler();

        $views->each(function (SplFileInfo $file) use ($compiler) {
            $compiler->compile($file->getRealPath());
        });
    }

    /**
     * @param array $paths
     *
     * @return \Illuminate\Support\Collection
     * @throws \InvalidArgumentException
     */
    protected function bladeFilesIn(array $paths): Collection
    {
        return new Collection(
            Finder::create()
                ->in($paths)
                ->exclude('vendor')
                ->name('*.blade.php')
                ->files()
        );
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function paths(): Collection
    {
        $finder = $this->view->getFinder();

        return (new Collection($finder->getPaths()))->merge(
            (new Collection($finder->getHints()))->flatten()
        );
    }
}

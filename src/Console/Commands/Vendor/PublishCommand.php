<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Vendor;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use League\Flysystem\MountManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Adapter\Local as LocalAdapter;

use function array_merge;
use function dirname;
use function explode;
use function preg_filter;
use function realpath;
use function strip_tags;
use function str_replace;

use const null;
use const true;

/**
 * Class PublishCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Vendor
 */
class PublishCommand extends Command
{
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var string|null
     */
    protected $provider;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var string
     */
    protected $signature = 'vendor:publish
                    {--force : Overwrite any existing files}
                    {--all : Publish assets for all service providers without prompt}
                    {--provider= : The service provider that has assets you want to publish}
                    {--tag=* : One or many tags that have assets you want to publish}';

    /**
     * @var string
     */
    protected $description = 'Publish any publishable assets from vendor packages';

    /**
     * PublishCommand constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FilesystemNotFoundException
     * @throws \LogicException
     */
    public function handle(): void
    {
        $this->determineWhatShouldBePublished();

        foreach ($this->tags ?: [null] as $tag) {
            $this->publishTag($tag);
        }

        $this->info('Publishing complete.');
    }

    /**
     * @return void
     */
    protected function determineWhatShouldBePublished(): void
    {
        if ($this->option('all')) {
            return;
        }

        [$this->provider, $this->tags] = [
            $this->option('provider'),
            (array) $this->option('tag'),
        ];

        if (!$this->provider && !$this->tags) {
            $this->promptForProviderOrTag();
        }
    }

    /**
     * @return void
     */
    protected function promptForProviderOrTag(): void
    {
        $choice = $this->choice(
            "Which provider or tag's files would you like to publish?",
            $choices = $this->publishableChoices()
        );

        if (null === $choice || $choice === $choices[0]) {
            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * @return array
     */
    protected function publishableChoices(): array
    {
        return array_merge(
            ['<comment>Publish files from all providers and tags listed below</comment>'],
            preg_filter(
                '/^/',
                '<comment>Provider: </comment>',
                Arr::sort(ServiceProvider::publishableProviders())
            ),
            preg_filter(
                '/^/',
                '<comment>Tag: </comment>',
                Arr::sort(ServiceProvider::publishableGroups())
            )
        );
    }

    /**
     * @param string $choice
     *
     * @return void
     */
    protected function parseChoice(string $choice): void
    {
        [$type, $value] = explode(': ', strip_tags($choice));

        if ($type === 'Provider') {
            $this->provider = $value;
        } elseif ($type === 'Tag') {
            $this->tags = [$value];
        }
    }

    /**
     * @param string|null $tag
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FilesystemNotFoundException
     * @throws \LogicException
     */
    protected function publishTag(string $tag = null): void
    {
        foreach ($this->pathsToPublish($tag) as $from => $to) {
            $this->publishItem($from, $to);
        }
    }

    /**
     * @param string|null $tag
     *
     * @return array
     */
    protected function pathsToPublish(string $tag = null): array
    {
        return ServiceProvider::pathsToPublish($this->provider, $tag);
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FilesystemNotFoundException
     * @throws \LogicException
     */
    protected function publishItem(string $from, string $to): void
    {
        if ($this->files->isFile($from)) {
            $this->publishFile($from, $to);

            return;
        }

        if ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);

            return;
        }

        $this->error("Can't locate path: <{$from}>");
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return void
     */
    protected function publishFile(string $from, string $to): void
    {
        if (!$this->files->exists($to) || $this->option('force')) {
            $this->createParentDirectory(dirname($to));

            $this->files->copy($from, $to);

            $this->status($from, $to, 'File');
        }
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FilesystemNotFoundException
     * @throws \LogicException
     */
    protected function publishDirectory(string $from, string $to): void
    {
        $this->moveManagedFiles(new MountManager([
            'from' => new Flysystem(new LocalAdapter($from)),
            'to' => new Flysystem(new LocalAdapter($to)),
        ]));

        $this->status($from, $to, 'Directory');
    }

    /**
     * @param \League\Flysystem\MountManager $manager
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FilesystemNotFoundException
     */
    protected function moveManagedFiles(MountManager $manager): void
    {
        foreach ($manager->listContents('from://', true) as $file) {
            if ($file['type'] === 'file' &&
                (!$manager->has('to://'.$file['path']) || $this->option('force'))
            ) {
                $manager->put(
                    'to://'.$file['path'],
                    $manager->read('from://'.$file['path'])
                );
            }
        }
    }

    /**
     * @param string $directory
     *
     * @return void
     */
    protected function createParentDirectory(string $directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $type
     *
     * @return void
     */
    protected function status(string $from, string $to, string $type): void
    {
        $basePath = $this->laravel->basePath();

        $from = str_replace($basePath, '', realpath($from));
        $to = str_replace($basePath, '', realpath($to));

        $this->line(
            "<info>Copied {$type}</info> ".
            "<comment>[{$from}}]</comment> ".
            '<info>To</info>'.
            "<comment>[{$to}}]</comment>"
        );
    }
}

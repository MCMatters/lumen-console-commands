<?php

declare(strict_types=1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Application;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Encryption\Encrypter;
use Laravel\Lumen\Application;

use function base64_encode;
use function env;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function preg_quote;

use const false;

/**
 * Class KeyGenerateCommand
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Application
 */
class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'key:generate
        {--show : Display the key instead of modifying files}
        {--force : Force the operation to run when in production}';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * KeyGenerateCommand constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->app = $app;
        $this->config = $app->make('config');
    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function handle(): void
    {
        $key = $this->generateKey();

        if ($this->option('show')) {
            $this->line("<comment>{$key}</comment>");

            return;
        }

        if (!$this->checkRequirements()) {
            return;
        }

        if ($this->writeKey($key) === false) {
            $this->error('Cannot write to file new key');

            return;
        }

        $this->info("Application key [$key] set successfully.");
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    protected function generateKey(): string
    {
        $key = Encrypter::generateKey($this->config->get('app.cipher', 'AES-128-CBC'));

        return 'base64:'.base64_encode($key);
    }

    /**
     * @return bool
     */
    protected function checkRequirements(): bool
    {
        // Check environment and existence of the key.
        return !($this->getCurrentKey() && !$this->confirmToProceed());
    }

    /**
     * @param string $key
     *
     * @return bool|int
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function writeKey(string $key)
    {
        $file = $this->getEnvironmentFile();

        $content = preg_replace(
            $this->getReplacementPattern(),
            "APP_KEY={$key}",
            file_get_contents($file)
        );

        return file_put_contents($file, $content);
    }

    /**
     * @return string
     */
    protected function getReplacementPattern(): string
    {
        $escaped = preg_quote("={$this->getCurrentKey()}", '/');

        return "/^APP_KEY{$escaped}/m";
    }

    /**
     * @return string|null
     */
    protected function getCurrentKey(): ?string
    {
        return env('APP_KEY', $this->config->get('app.key'));
    }

    /**
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getEnvironmentFile(): string
    {
        $file = $this->config->get(
            'console-commands.env',
            $this->app->basePath().DIRECTORY_SEPARATOR.'.env'
        );

        if (!file_exists($file)) {
            throw new FileNotFoundException("There is no file {$file}");
        }

        return $file;
    }
}

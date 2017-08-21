<?php

declare(strict_types = 1);

namespace McMatters\LumenConsoleCommands\Console\Commands\Application;

use Dotenv\Loader;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Laravel\Lumen\Application;
use const false;
use function base64_encode, env, file_exists, file_get_contents,
    file_put_contents, preg_replace, preg_quote, random_bytes;

/**
 * Class KeyGenerate
 *
 * @package McMatters\LumenConsoleCommands\Console\Commands\Application
 */
class KeyGenerate extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'key:generate
        {--show : Display the key instead of modifying files}
        {--force : Force the operation to run when in production}';

    /**
     * @var Application
     */
    protected $app;

    /**
     * KeyGenerate constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        parent::__construct();
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    public function handle()
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

        $this->updateAppKey($key);

        $this->info("Application key [$key] set successfully.");
    }

    /**
     * @return string
     */
    protected function generateKey(): string
    {
        $bytes = $this->app['config']->get('app.cipher') === 'AES-128-CBC'
            ? 16
            : 32;

        return 'base64:'.base64_encode(random_bytes($bytes));
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
     * @throws FileNotFoundException
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
    protected function getCurrentKey()
    {
        return env('APP_KEY', $this->app['config']->get('app.key'));
    }

    /**
     * @return string
     * @throws FileNotFoundException
     */
    protected function getEnvironmentFile(): string
    {
        $file = $this->app['config']->get(
            'lumen-commands.env',
            $this->app->basePath().DIRECTORY_SEPARATOR.'.env'
        );

        if (!file_exists($file)) {
            throw new FileNotFoundException("There is no file {$file}");
        }

        return $file;
    }

    /**
     * @param string $key
     *
     * @throws FileNotFoundException
     */
    protected function updateAppKey(string $key)
    {
        $this->app['config']->set('app.key', $key);
        (new Loader($this->getEnvironmentFile(), true))->setEnvironmentVariable(
            'APP_KEY',
            $key
        );
    }
}

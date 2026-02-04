<?php

namespace Riki;

use DependencyInjector\Container;
use DependencyInjector\DI;

/**
 * Class Application
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 *
 * @method static static app()
 * @method static Environment environment()
 * @method static Config config()
 * @property-read Container $app
 * @property-read Environment $environment
 * @property-read Config $config
 */
abstract class Application extends Container
{
    protected static ?Application $app = null;

    /** Overwrite this to change the path where the config cache is stored (defaults to <cache>/config.dat)  */
    protected ?string $configCachePath = null;

    /**
     * Application Constructor
     *
     * @param Environment $environment
     * @throws \Throwable
     */
    public function __construct(Environment $environment)
    {
        if (static::$app !== null) {
            throw new Exception('There can only be one application at the same time');
        }
        static::$app = $this;

        try {
            parent::__construct();
            DI::setContainer($this);

            $this->instance(Application::class, $this);
            $this->instance('app', $this);
            $this->instance('environment', $environment);
            $this->loadConfiguration();
            $this->initDependencies();
        } catch (\Throwable $error) {
            $this->bootError($error);
        }

    }

    /**
     * Called when an error occurs during bootstrapping
     *
     * @param \Throwable $error
     * @throws \Throwable
     * @codeCoverageIgnore trivial code
     */
    protected function bootError(\Throwable $error)
    {
        throw $error;
    }

    /**
     * Destroy the application
     *
     * After destroying you can create a new application.
     */
    public function destroy(): void
    {
        static::$app = null;
        $this->factories = [];
        $this->namespaces = [];
    }

    /**
     * Static method to get named dependencies.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @codeCoverageIgnore trivial code
     */
    public static function __callStatic(string $method, array $args)
    {
        if (static::$app === null) {
            throw new Exception('Application not initialized');
        }
        return static::$app->get($method, ...$args);
    }

    /**
     * Defines all dependencies / namespaces / aliases
     */
    protected function initDependencies()
    {
    }

    /**
     * Bootstrap and run $kernel with $args
     *
     * There is no logic to skip bootstrappers or stop if $kernel was already executed. This logic is related to your
     * Kernel and needs to be added there if needed.
     *
     * @param Kernel $kernel
     * @param mixed  ...$args Arguments to be passed to $kernel->handle()
     * @return mixed
     * @throws \Exception
     */
    public function run(Kernel $kernel, ...$args)
    {
        return $kernel->handle(...$args);
    }

    /**
     * Loads the configuration
     *
     * When caching is enabled and a cached config exists this will be loaded otherwise a new object will be
     * initialized.
     *
     * @return bool
     */
    protected function loadConfiguration(): bool
    {
        $config = $this->loadConfigurationCache() ?? $this->generateConfiguration();
        $this->instance('config', $config);
        $this->alias('config', Config::class);
        return true;
    }

    /**
     * Loads the cached configuration
     *
     * @return Config|null
     */
    protected function loadConfigurationCache(): ?Config
    {
        $cachePath = $this->configCachePath ?? $this->get('environment')->cachePath('config.dat');
        if (!$cachePath || !is_readable($cachePath) || is_dir($cachePath)) {
            return null;
        }

        /** @var Config $config */
        $config = @unserialize(file_get_contents($cachePath));

        return  $config instanceof Config ? $config : null;
    }

    /**
     * Generates a new configuration object
     *
     * @return Config
     */
    protected function generateConfiguration(): Config
    {
        /** @var Environment $environment */
        $environment = $this->get('environment');
        $environment->loadEnvironment();
        return Config::fromFiles($this->locateConfigFiles($environment->configPath()), $environment);
    }

    /**
     * Rebuilds the configuration cache
     *
     * @return bool
     */
    public function rebuildConfigurationCache(): bool
    {
        $config = $this->generateConfiguration();
        $cachePath = $this->configCachePath ?? $this->get('environment')->cachePath('config.dat');

        $cacheDir = dirname($cachePath);
        if (is_file($cacheDir) || !is_dir($cacheDir) && !mkdir($cacheDir, 0777, true)) {
            return false;
        }

        return file_put_contents($cachePath, serialize($config)) > 0;
    }

    /**
     * Locates the config files in the given path
     *
     * @param string $path
     * @return array
     */
    protected function locateConfigFiles(string $path): array
    {
        if (is_dir($path)) {
            return array_reduce(glob($path . '/*.php'), function ($carry, $item) {
                $name = pathinfo($item, PATHINFO_FILENAME);
                $carry[$name] = $item;
                return $carry;
            }, []);
        }
        return [];
    }
}

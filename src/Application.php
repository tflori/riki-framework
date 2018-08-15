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
    /** @var Application */
    protected static $app;

    /** @var string */
    protected $basePath;

    /** @var string  */
    protected static $fallbackEnvironment = 'App\Environment';

    /** @var string  */
    protected static $environmentNamespace = 'App\Environment';

    /** @var string */
    protected static $configClass = 'App\Config';

    /**
     * Application Constructor
     *
     * @param string $basePath
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        if (static::$app) {
            throw new Exception('There can only be one application at the same time');
        }
        static::$app = $this;

        $this->basePath = $basePath;

        parent::__construct();
        DI::setContainer($this);

        $this->initDependencies();
        $this->detectEnvironment();
        $this->loadConfiguration();
    }

    /**
     * Destroy the application
     *
     * After destroying you can create a new application.
     */
    public function destroy()
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
    public static function __callStatic($method, $args)
    {
        return static::$app->get($method, ...$args);
    }

    /**
     * Defines all dependencies / namespaces / aliases
     */
    protected function initDependencies()
    {
        $this->instance(Application::class, $this);
        $this->instance('app', $this);
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
        $this->bootstrap(...$kernel->getBootstrappers());
        return $kernel->handle($this, ...$args);
    }

    /**
     * Execute the $bootstrappers
     *
     * Every bootstrapper needs to return a truthful value (e. g. true, 1 etc.).
     *
     * @param callable ...$bootstrappers
     * @throws Exception
     */
    protected function bootstrap(callable ...$bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            if (!call_user_func($bootstrapper, $this)) {
                throw new Exception(sprintf(
                    '%s failed for unknown reason',
                    $this->getBootstrapperName($bootstrapper)
                ));
            }
        }
    }

    /**
     * Helper to get the name of a bootstrapper
     *
     * @param callable $cb
     * @return callable|string
     */
    protected function getBootstrapperName(callable $cb)
    {
        $prefix = 'Bootstrapper ';
        if (is_array($cb)) {
            list($obj, $method) = $cb;
            $class = is_object($obj) ? get_class($obj) : $obj;
            return $prefix . $class . '::' . $method;
        } elseif (is_string($cb)) {
            return $prefix . $cb;
        } elseif (is_object($cb) && !$cb instanceof \Closure) {
            return $prefix . get_class($cb);
        } else {
            return 'Unknown bootstrapper';
        }
    }

    /**
     * Detects the environment for APP_ENV environment variable or 'development'
     *
     * When there is a *Cli environment and this is executed via command line it prefers *Cli.
     *
     * @return bool
     * @throws Exception
     */
    public function detectEnvironment(): bool
    {
        $classes = [ static::$fallbackEnvironment ];
        $appEnv = getenv('APP_ENV') ?: 'development';
        $classes[] = static::$environmentNamespace . '\\' . ucfirst($appEnv);
        if (PHP_SAPI === 'cli') {
            $classes[] = static::$environmentNamespace . '\\' . ucfirst($appEnv) . 'Cli';
        }
        foreach (array_reverse($classes) as $class) {
            if (class_exists($class)) {
                $this->instance('environment', new $class($this->getBasePath()));
                $this->alias('environment', static::$fallbackEnvironment);
                return true;
            }
        }

        throw new Exception('No environment found');
    }

    /**
     * Loads the configuration
     *
     * When caching is enabled and a cached config exists this will be loaded otherwise a new object will be
     * initialized.
     *
     * @return bool
     * @throws Exception
     */
    public function loadConfiguration(): bool
    {
        /** @var \Riki\Environment $environment */
        $environment = $this->get('environment');

        $cachePath = $environment->getConfigCachePath();
        if ($environment->canCacheConfig() && is_readable($cachePath) && !is_dir($cachePath)) {
            /** @var Config $config */
            $config = unserialize(file_get_contents($cachePath));
            $config->environment = $environment;
        } elseif (class_exists(static::$configClass)) {
            $config =  new static::$configClass($environment);
        } else {
            throw new Exception('Configuration not found');
        }

        $this->instance('config', $config);
        $this->alias('config', static::$configClass);
        return true;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}

<?php

namespace Riki;

use DependencyInjector\Container;

/**
 * Class Application
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 *
 * @property-read Container $app
 * @property-read Environment $environment
 * @property-read Config $config
 */
abstract class Application extends Container
{
    use WithBootstrappers;

    /** @var string */
    protected $basePath;

    /** @var string  */
    protected $fallbackEnvironment = 'App\Environment';

    /** @var string  */
    protected $environmentNamespace = 'App\Environment';

    /** @var string */
    protected $configClass = 'App\Config';

    /**
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        parent::__construct();
        $this->alias('container', Application::class);
        $this->alias('container', 'app');
        \DependencyInjector\DI::setContainer($this);

        $this->basePath = $basePath;

        $this->addBootstrappers(
            [$this, 'detectEnvironment'],
            [$this, 'loadConfig']
        );
    }

    /**
     * @param Kernel $kernel
     * @param mixed  ...$args
     * @return mixed
     * @throws \Exception
     */
    public function run(Kernel $kernel, ...$args)
    {
        $this->bootstrap(...$this->getBootstrappers(), ...$kernel->getBootstrappers());
        return $kernel->handle(...$args);
    }

    /**
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
     * Detects the environment for APP_ENV environment variable or 'Development'
     *
     * When there is a *Cli environment and this is executed via command line it prefers *Cli.
     *
     * @param Application $app
     * @return bool
     * @throws Exception
     */
    public function detectEnvironment(Application $app): bool
    {
        if ($app->has('environment')) {
            return true;
        }

        $classes = [ $this->fallbackEnvironment ];
        $appEnv = getenv('APP_ENV') ?: 'development';
        $classes[] = $this->environmentNamespace . '\\' . ucfirst($appEnv);
        if (PHP_SAPI === 'cli') {
            $classes[] = $this->environmentNamespace . '\\' . ucfirst($appEnv) . 'Cli';
        }
        foreach (array_reverse($classes) as $class) {
            if (class_exists($class)) {
                $app->instance('environment', new $class($this->basePath));
                $app->alias('environment', $this->fallbackEnvironment);
                return true;
            }
        }

        throw new Exception('No environment found');
    }

    /**
     * @param Application $app
     * @return bool
     * @throws Exception
     */
    public function loadConfig(Application $app): bool
    {
        if ($app->has('config')) {
            return true;
        }

        /** @var \Riki\Environment $environment */
        $environment = $app->get('environment');
        $cachePath = $environment->getConfigCachePath();
        if ($environment->canCacheConfig() && is_readable($cachePath) && !is_dir($cachePath)) {
            /** @var Config $config */
            $config = unserialize(file_get_contents($cachePath));
            $config->environment = $environment;
        } elseif (class_exists($this->configClass)) {
            $config =  new $this->configClass($environment);
        } else {
            throw new Exception('Configuration not found');
        }

        $app->instance('config', $config);
        $app->alias('config', $this->configClass);
        return true;
    }
}

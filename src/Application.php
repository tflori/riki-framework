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
        $bootstrappers = $this->getBootstrappers();
        array_push($bootstrappers, ...$kernel->getBootstrappers());
        $this->bootstrap(...$bootstrappers);
        return $kernel->handle(...$args);
    }

    /**
     * @param callable ...$bootstrappers
     * @throws Exception
     */
    protected function bootstrap(callable ...$bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            try {
                if (!$bootstrapper($this)) {
                    throw new \Exception('Unknown error');
                }
            } catch (\Throwable | \Exception $ex) {
                throw new Exception('Unexpected exception in bootstrap process', 0, $ex);
            }
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
        $environment = $this->get('environment');
        $cachePath = $environment->getConfigCachePath();
        if ($environment->canCacheConfig() && is_readable($cachePath) && !is_dir($cachePath)) {
            /** @var Config $config */
            $config = unserialize(file_get_contents($cachePath));
            $config->environment = $environment;
        } else {
            $class = $this->configClass;
            $config =  new $class($environment);
        }

        if (!$config) {
            throw new Exception('Configuration not found');
        }

        $this->instance('config', $config);
        $this->alias('config', $this->configClass);
        return true;
    }
}

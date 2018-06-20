<?php

namespace Riki;

use Symfony\Component\Dotenv\Dotenv;

/**
 * Class Config
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 */
abstract class Config
{
    /** @var Environment */
    public $environment;

    /** @var array */
    protected $env = [];

    /**
     * Loads .env and stores the current environment
     *
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->loadDotEnv();
        $this->env = $_ENV;
        unset($this->env['SYMFONY_DOTENV_VARS']);
    }

    /**
     * Get an environment variable (also works from cached config)
     *
     * @param string $name
     * @param null   $default
     * @return mixed
     */
    public function env(string $name, $default = null)
    {
        return $this->env[$name] ?? $default;
    }

    /**
     * Loads the .env file configured in environment
     *
     * @return bool
     */
    protected function loadDotEnv(): bool
    {
        if (!$this->environment->usesDotEnv()) {
            return false;
        }

        $dotEnvPath = $this->environment->getDotEnvPath();
        if (!is_readable($dotEnvPath) || is_dir($dotEnvPath)) {
            return false;
        }

        putenv('BASE_PATH=' . $this->environment->getBasePath());
        $dotEnv = new Dotenv();
        $dotEnv->load($dotEnvPath);
        return true;
    }

    // CACHING

    public function cache(): bool
    {
        if ($this->environment->canCacheConfig()) {
            return file_put_contents($this->environment->getConfigCachePath(), serialize($this));
        }

        return false;
    }

    public function __sleep()
    {
        $this->environment = null;
        return array_keys(get_class_vars(static::class));
    }
}

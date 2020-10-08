<?php

namespace Riki;

use EnvParser\EnvFile;

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

    /** @var EnvFile */
    protected $env;

    /**
     * Loads .env and stores the current environment
     *
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->loadDotEnv();
    }

    /**
     * Get an environment variable (also works from cached config)
     *
     * @param ?string $name
     * @param ?mixed  $default
     * @return mixed
     */
    public function env(string $name = null, $default = null)
    {
        if (!$this->env instanceof EnvFile) {
            $this->loadDotEnv();
        }

        if (is_null($name)) {
            return $this->env->getArrayCopy();
        }

        return $this->env->get($name, $default);
    }

    /**
     * Loads the .env file configured in environment
     *
     * @return bool
     */
    protected function loadDotEnv(): bool
    {
        $this->env = new EnvFile();
        if (!$this->environment->usesDotEnv()) {
            return false;
        }

        $dotEnvPath = $this->environment->getDotEnvPath();
        if (!is_readable($dotEnvPath) || is_dir($dotEnvPath)) {
            return false;
        }

        putenv('BASE_PATH=' . $this->environment->getBasePath());
        $this->env->read($dotEnvPath);
        return true;
    }

    /**
     * Ensures the environment does not get serialized for caching
     *
     * @return array
     */
    public function __sleep()
    {
        $vars = array_keys(get_class_vars(static::class));
        array_splice($vars, array_search('environment', $vars), 1);
        return $vars;
    }
}

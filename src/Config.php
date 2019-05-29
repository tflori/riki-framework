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
        $value = $_ENV[$name] ?? $this->env[$name] ?? $default;

        if (is_string($value)) {
            if (is_numeric($value)) {
                return (double)$value === round((double)$value) ? (int)$value : (double)$value;
            }

            if (in_array($value, ['true', 'false'], true)) {
                return $value === 'true';
            }
        }

        return $value;
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

<?php

namespace Riki;


use Symfony\Component\Dotenv\Dotenv;

class Config
{
    /** @var Environment */
    public $environment;

    /** @var array */
    protected $env = [];

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->loadDotEnv();
        $this->env = $_ENV;
        unset($this->env['SYMFONY_DOTENV_VARS']);
    }

    public function env(string $name, $default = null)
    {
        return $this->env[$name] ?? $default;
    }

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

    public static function restoreOrCreate(Environment $environment): Config
    {
        if ($environment->canCacheConfig()) {
            $cachePath = $environment->getConfigCachePath();

            if (is_readable($cachePath) && !is_dir($cachePath)) {
                $config = unserialize(file_get_contents($cachePath));
                $config->environment = $environment;
                return $config;
            }
        }

        return new static($environment);
    }

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

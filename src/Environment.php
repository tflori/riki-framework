<?php

namespace Riki;

abstract class Environment
{
    /** @var string */
    protected $basePath;

    public function getBasePath(): string
    {
        if (!$this->basePath) {
            $reflection = new \ReflectionClass($this);
            $this->basePath = dirname(dirname(dirname($reflection->getFileName())));
        }

        return $this->basePath;
    }

    public function canCacheConfig(): bool
    {
        return true;
    }

    public function getConfigCachePath(): string
    {
        return $this->getBasePath() . '/.config.cache';
    }

    public function usesDotEnv(): bool
    {
        return true;
    }

    public function getDotEnvPath()
    {
        return $this->getBasePath() . '/.env';
    }

    public static function init($namespace = null)
    {
        if (!$namespace) {
            $reflection = new \ReflectionClass(static::class);
            if ($reflection->getNamespaceName() === __NAMESPACE__) {
                return new static;
            }
            $namespace = $reflection->getNamespaceName();
        }

        $envClass = $namespace . '\\' . ucfirst(getenv('APP_ENV') ?: 'development');
        $classes = [$envClass];
        if (PHP_SAPI === 'cli') {
            $classes[] = $envClass . 'Cli';
        }

        foreach (array_reverse($classes) as $class) {
            if (class_exists($class)) {
                return new $class;
            }
        }

        return new static;
    }
}

<?php

namespace Riki;

use EnvParser\EnvFile;

/**
 * Class Environment
 *
 * Other then the configurations of the environment file this configurations are specific to an environment type (e. g.
 * a development environment) and can be inherited.
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 */
class Environment implements \ArrayAccess
{
    /** @var string */
    protected $installPath;
    protected $configPath;
    protected $resourcesPath;
    protected $storagePath;

    /** @var EnvFile */
    protected $environment;
    protected $envLoaded = false;

    /**
     * @param string $installPath
     */
    public function __construct(string $installPath) {
        $this->installPath = $installPath;
        $this->storagePath = $this->path('storage');
        $this->configPath = $this->path('config');
        $this->resourcesPath = $this->path('resources');
        $this->environment = new EnvFile(getenv());
    }

    public function resourcePath(string ...$path)
    {
        array_unshift($path, $this->resourcesPath);
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public function storagePath(string ...$path)
    {
        array_unshift($path, $this->storagePath);
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public function cachePath(string ...$path)
    {
        return $this->storagePath('cache', ...$path);
    }

    public function configPath(string ...$path)
    {
        array_unshift($path, $this->configPath);
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public function path(string ...$path): string
    {
        array_unshift($path, $this->installPath);
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public function loadEnvironment($search = false)
    {
        if ($this->envLoaded) {
            return;
        }

        $appEnv = getenv('APP_ENV') ?? null;
        $appEnv = ($appEnv === false) ? '' : trim((string) $appEnv);
        $path = $this->installPath;
        do {
            $files = [$path . '/.env'];
            if ($appEnv !== '') {
                array_unshift($files, $path . '/.env.' . $appEnv);
            }
            foreach ($files as $file) {
                if (file_exists($file) && is_readable($file) && is_file($file)) {
                    $this->envLoaded = true;
                    $this->environment->read($file);
                    return;
                }
            }
            if (!$search) {
                return;
            }
        } while (($path = dirname($path)) !== '/');
    }

    public function get(string $var, $default = null)
    {
        return $this->environment->get($var, $default);
    }

    public function offsetExists($offset): bool
    {
        return $this->environment->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->environment->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        // noop - we can't change the environment
    }

    public function offsetUnset($offset)
    {
        // noop - we can't change the environment
    }
}

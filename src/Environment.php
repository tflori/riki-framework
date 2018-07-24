<?php

namespace Riki;

/**
 * Class Environment
 *
 * Other then the configurations of the environment file this configurations are specific to an environment type (e. g.
 * a development environment) and can be inherited.
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 */
abstract class Environment
{
    /** @var string */
    protected $basePath;

    /**
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function getBasePath(): string
    {
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
}

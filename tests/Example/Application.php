<?php

namespace Riki\Test\Example;

use Riki\Config;
use Riki\Environment;

class Application extends \Riki\Application
{
    protected static string $configClass = Config::class;

    public function __construct(
        Environment $environment,
        array $options = []
    ) {
        static::$configClass = $options['configClass'] ?? Config::class;
        $this->configCachePath = $options['cachePath'] ?? null;
        parent::__construct($environment);
    }

    protected function generateConfiguration(): Config
    {
        /** @var Environment $environment */
        $environment = $this->get('environment');
        $environment->loadEnvironment();
        return self::$configClass::fromFiles($this->locateConfigFiles($environment->configPath()), $environment);
    }
}

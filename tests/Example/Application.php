<?php

namespace Riki\Test\Example;

use Riki\Test\Example\Environment\Fallback;

class Application extends \Riki\Application
{
    protected static $fallbackEnvironment = Fallback::class;
    protected static $environmentNamespace = Environment::class;
    protected static $configClass = Config::class;

    public function __construct(
        string $basePath,
        string $fallbackEnvironment = Fallback::class,
        string $configClass = Config::class
    ) {
        static::$fallbackEnvironment = $fallbackEnvironment;
        static::$configClass = $configClass;
        parent::__construct($basePath);
    }
}

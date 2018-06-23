<?php

namespace Riki\Test\Example;

use Riki\Test\Example\Environment\Fallback;

class Application extends \Riki\Application
{
    protected $fallbackEnvironment = Fallback::class;
    protected $environmentNamespace = Environment::class;
    protected $configClass = Config::class;

    public function __construct(
        string $basePath,
        string $fallbackEnvironment = Fallback::class,
        string $configClass = Config::class
    ) {
        parent::__construct($basePath);
        $this->fallbackEnvironment = $fallbackEnvironment;
        $this->configClass = $configClass;
    }
}

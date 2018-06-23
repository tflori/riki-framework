<?php

namespace Riki\Test\Application;

use Riki\Application;
use Riki\Test\Application\Environment\Fallback;
use Riki\Test\Config\Example as ConfigExample;

class Example extends Application
{
    protected $fallbackEnvironment = Fallback::class;
    protected $environmentNamespace = Environment::class;
    protected $configClass = ConfigExample::class;

    public function __construct(
        string $basePath,
        string $fallbackEnvironment = Fallback::class,
        string $configClass = ConfigExample::class
    ) {
        parent::__construct($basePath);
        $this->fallbackEnvironment = $fallbackEnvironment;
        $this->configClass = $configClass;
    }
}

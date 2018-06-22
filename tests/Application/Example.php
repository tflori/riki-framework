<?php

namespace Riki\Test\Application;

use Riki\Application;
use Riki\Test\Application\Environment\Fallback;

class Example extends Application
{
    protected $fallbackEnvironment = Fallback::class;
    protected $environmentNamespace = Environment::class;

    public function __construct(string $basePath, string $fallbackEnvironment = Fallback::class)
    {
        parent::__construct($basePath);
        $this->fallbackEnvironment = $fallbackEnvironment;
    }
}

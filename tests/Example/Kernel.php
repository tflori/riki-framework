<?php

namespace Riki\Test\Example;

use Riki\Application;

class Kernel extends \Riki\Kernel
{
    protected $executions = 0;

    public function __construct()
    {
        $this->addBootstrappers([$this, 'reset']);
    }

    public function handle(Application $app, $request = null)
    {
        $this->executions++;
        return $this->executions === 1;
    }

    public function reset()
    {
        $this->executions = 0;
        return true;
    }
}

<?php

namespace Riki\Test\Example;

use Riki\Environment as BaseEnvironment;

class Environment extends BaseEnvironment
{
    public function isEnvLoaded()
    {
        return $this->envLoaded;
    }
}

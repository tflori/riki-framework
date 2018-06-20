<?php

namespace Riki;

abstract class Kernel
{
    use WithBootstrappers;

    /**
     * @return mixed
     */
    abstract public function handle();
}

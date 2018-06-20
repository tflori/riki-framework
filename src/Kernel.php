<?php

namespace Riki;

abstract class Kernel
{
    use WithBootstrappers;

    /**
     * @param mixed ...$args
     * @return mixed
     */
    abstract public function handle(...$args);
}

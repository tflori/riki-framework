<?php

namespace Riki;

/**
 * Class Kernel
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 */
abstract class Kernel
{
    /**
     * Kernel constructor.
     *
     * @param Application $app
     * @codeCoverageIgnore
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle the execution
     *
     * @param mixed ...$args
     * @return mixed
     */
    abstract public function handle(...$args);
}

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
    /** @var Application */
    protected $app;

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
     * Handle the request using $app
     *
     * @return mixed
     */
    abstract public function handle();
}

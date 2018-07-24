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
    /** @var callable[] */
    protected $bootstrappers = [];

    /**
     * Add bootstrappers for this Kernel
     *
     * @param callable ...$bootstrapper
     * @return $this
     */
    public function addBootstrappers(callable ...$bootstrapper)
    {
        array_push($this->bootstrappers, ...$bootstrapper);
        return $this;
    }

    public function getBootstrappers(): array
    {
        return $this->bootstrappers;
    }

    /**
     * Handle the request using $app
     *
     * @param Application $app
     * @return mixed
     */
    abstract public function handle(Application $app);
}

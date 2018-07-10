<?php

namespace Riki;

abstract class Kernel
{
    /** @var callable[] */
    protected $bootstrappers = [];

    /**
     * @param callable ...$bootstrapper
     * @return $this
     */
    public function addBootstrappers(callable ...$bootstrapper)
    {
        array_push($this->bootstrappers, ...$bootstrapper);
        return $this;
    }

    /**
     * @return callable[]|array
     */
    public function getBootstrappers()
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

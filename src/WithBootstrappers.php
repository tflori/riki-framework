<?php

namespace Riki;

trait WithBootstrappers
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
}

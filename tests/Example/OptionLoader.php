<?php

namespace Riki\Test\Example;

class OptionLoader
{
    public function __invoke()
    {
        return false;
    }

    public static function loadStatic()
    {
        return false;
    }

    public function load()
    {
        return false;
    }
}

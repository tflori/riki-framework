<?php

namespace Riki\Test\Config;

use Riki\Config;
use Riki\Environment;

class Example extends Config
{
    /** @var \stdClass */
    public $dbConfig;

    public $key = 'randomKey';

    public function __construct(Environment $environment)
    {
        parent::__construct($environment);
        $this->dbConfig = (object)[
            'host' => 'localhost',
            'port' => 3306,
            'user' => 'john',
            'pass' => 'secret'
        ];
    }
}

<?php

namespace Riki\Test\Example;

use Riki\Environment;

class Config extends \Riki\Config
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

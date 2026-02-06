<?php

namespace Riki\Test\Example;

/**
 * @deprecated you don't need to create own config classes anymore but you can still use them
 */
class Config extends \Riki\Config
{
    /** @var \stdClass */
    public $dbConfig;

    public $key = 'randomKey';

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->dbConfig = (object)[
            'host' => 'localhost',
            'port' => 3306,
            'user' => 'john',
            'pass' => 'secret'
        ];
    }
}

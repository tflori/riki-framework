<?php

namespace Riki\Test\Config;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Environment;
use Riki\Test\Example\Config;

class SerializationTest extends MockeryTestCase
{
    /** @var m\Mock|Environment */
    protected $env;

    protected function setUp()
    {
        parent::setUp();
        $this->env = m::mock(Environment::class);
        $this->env->shouldReceive('usesDotEnv')->andReturn(false)->byDefault();
    }

    /** @test */
    public function doesNotContainTheEnvironment()
    {
        $config = new Config($this->env);
        $serialized = serialize($config);

        self::assertNotContains('environment', $serialized);
    }

    /** @test */
    public function containsConstructedVars()
    {
        $config = new Config($this->env);
        $serialized = serialize($config);

        self::assertContains('host', $serialized);
        self::assertContains('secret', $serialized);
    }

    /** @test */
    public function containsSimpleVars()
    {
        $config = new Config($this->env);
        $serialized = serialize($config);

        self::assertContains('randomKey', $serialized);
    }
}

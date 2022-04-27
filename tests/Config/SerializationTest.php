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

    protected function setUp(): void
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

        self::assertStringNotContainsString('environment', $serialized);
    }

    /** @test */
    public function containsConstructedVars()
    {
        $config = new Config($this->env);
        $serialized = serialize($config);

        self::assertStringContainsString('host', $serialized);
        self::assertStringContainsString('secret', $serialized);
    }

    /** @test */
    public function containsSimpleVars()
    {
        $config = new Config($this->env);
        $serialized = serialize($config);

        self::assertStringContainsString('randomKey', $serialized);
    }
}

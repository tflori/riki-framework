<?php

namespace Riki\Test\Config;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Environment;
use Mockery as m;

class LoadDotEnvTest extends MockeryTestCase
{
    /** @var m\Mock|Environment */
    protected $env;

    protected function setUp()
    {
        parent::setUp();
        $this->env = m::mock(Environment::class);
        $this->env->shouldReceive('usesDotEnv')->andReturn(true)->byDefault();
        $this->env->shouldReceive('getDotEnvPath')->andReturn(__DIR__ . '/example.env')->byDefault();
        $this->env->shouldReceive('getBasePath')->andReturn(dirname(dirname(__DIR__)))->byDefault();
    }

    /** @test */
    public function checksIfEnvironmentUsesDotEnv()
    {
        $this->env->shouldReceive('usesDotEnv')->with()->once()->andReturn(false);

        new Example($this->env);
    }

    /** @test */
    public function getsDotEnvPathFromEnvironment()
    {
        $this->env->shouldReceive('getDotEnvPath')->with()->once()->andReturn(__DIR__ . '/not-existing.env');

        new Example($this->env);
    }

    /** @test */
    public function createsBasePathEnvironmentVariable()
    {
        $this->env->shouldReceive('getBasePath')->with()->once()->andReturn('/tmp');

        new Example($this->env);

        self::assertSame('/tmp', getenv('BASE_PATH'));
    }

    /** @test */
    public function loadsDotEnvFile()
    {
        $this->env->shouldReceive('getBasePath')->andReturn('/tmp');

        $config = new Example($this->env);
        $result = $config->env('STORAGE_PATH');

        self::assertSame('/tmp/storage', $result);
    }
}

<?php

namespace Riki\Test\Config;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Environment;
use Riki\Test\Example\Config;

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

        new Config($this->env);
    }

    /** @test */
    public function getsDotEnvPathFromEnvironment()
    {
        $this->env->shouldReceive('getDotEnvPath')->with()->once()->andReturn(__DIR__ . '/not-existing.env');

        new Config($this->env);
    }

    /** @test */
    public function createsBasePathEnvironmentVariable()
    {
        $this->env->shouldReceive('getBasePath')->with()->once()->andReturn('/tmp');

        new Config($this->env);

        self::assertSame('/tmp', getenv('BASE_PATH'));
    }

    /** @test */
    public function loadsDotEnvFile()
    {
        $this->env->shouldReceive('getBasePath')->andReturn('/tmp');

        $config = new Config($this->env);
        $result = $config->env('STORAGE_PATH');

        self::assertSame('/tmp/storage', $result);
    }

    /** @test */
    public function removesSymfonysDotEntVars()
    {
        $config = new Config($this->env);
        $result = $config->env('SYMFONY_DOTENV_VARS');

        self::assertNull($result);
    }

    /** @test */
    public function convertsIntegers()
    {
        $config = new Config($this->env);
        $result = $config->env('INTEGER');

        self::assertSame(42, $result);
    }

    /** @test */
    public function convertsFloats()
    {
        $config = new Config($this->env);
        $result = $config->env('FLOAT');

        self::assertSame(99.99, $result);
    }

    /** @test */
    public function convertsTrue()
    {
        $config = new Config($this->env);
        $result = $config->env('BOOLEAN_TRUE');

        self::assertTrue($result);
    }

    /** @test */
    public function convertsFalse()
    {
        $config = new Config($this->env);
        $result = $config->env('BOOLEAN_FALSE');

        self::assertFalse($result);
    }
}

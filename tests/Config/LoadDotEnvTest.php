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

    protected function setUp(): void
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
    public function returnsDefaultWhenMissing()
    {
        $config = new Config($this->env);
        $result = $config->env('ANOTHER_PATH', '/dev/null');

        self::assertSame('/dev/null', $result);
    }

    /** @test */
    public function returnsAnArrayWithAllEnvVars()
    {
        $config = new Config($this->env);
        $result = $config->env();

        self::assertArrayHasKey('STORAGE_PATH', $result);
    }

    /** @test */
    public function reloadsDotEnvFile()
    {
        $this->env->shouldReceive('getBasePath')->andReturn('/tmp');

        // for example the config got restored from serialization of old config
        $config = new Config($this->env);
        $this->setProtectedProperty($config, 'env', ['STORAGE_PATH' => '/any/path']);

        $result = $config->env('STORAGE_PATH');

        self::assertSame('/tmp/storage', $result);
    }

    /**
     * Overwrite a protected or private $property from $object to $value
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     */
    protected static function setProtectedProperty($object, string $property, $value)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $property = (new \ReflectionClass($object))->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}

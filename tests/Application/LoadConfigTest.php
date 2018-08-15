<?php

namespace Riki\Test\Application;

use DependencyInjector\NotFoundException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Config;
use Riki\Environment;
use Riki\Exception;
use Riki\Test\Example\Environment\Fallback;
use Riki\Test\Example\Application;
use Riki\Test\Example\Config as ConfigExample;

class LoadConfigTest extends MockeryTestCase
{
    /** @var Application */
    protected $app;

    /** @var Environment|m\Mock */
    protected $environment;

    protected function setUp()
    {
        $this->app = new Application(__DIR__);
        $this->environment = m::mock(Environment::class, [__DIR__])->makePartial();
        $this->app->instance('environment', $this->environment);
        $this->environment->shouldReceive('canCacheConfig')->with()->andReturn(true)->byDefault();
    }

    protected function tearDown()
    {
        if (file_exists('/tmp/config.ser')) {
            unlink('/tmp/config.ser');
        }
        Application::app()->destroy();
    }

    /** @test */
    public function storesTheConfigInstance()
    {
        $this->app->loadConfiguration();

        self::assertInstanceOf(Config::class, $this->app->config);
        self::assertInstanceOf(Config::class, $this->app->get(ConfigExample::class));
    }

    /** @test */
    public function loadsSerializedConfig()
    {
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/../Example/config.ser');

        $this->app->loadConfiguration();

        self::assertSame('Ie0g2aUbJi8=', $this->app->config->key);
    }

    /** @test */
    public function instantiatesNewConfig()
    {
        $this->environment->shouldReceive('canCacheConfig')->with()
            ->once()->andReturn(false);
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/../Example/config.ser');

        $this->app->loadConfiguration();

        self::assertNotSame('Ie0g2aUbJi8=', $this->app->config->key);
    }

    /** @test */
    public function setsTheCurrentEnvironment()
    {
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/../Example/config.ser');

        $this->app->loadConfiguration();

        self::assertSame($this->environment, $this->app->config->environment);
    }

    /** @test */
    public function throwsWhenConfigDoesNotExist()
    {
        Application::app()->destroy();
        self::expectException(Exception::class);
        self::expectExceptionMessage('Configuration not found');

        $app = new Application(__DIR__, Fallback::class, 'UnknownClass');
        $app->instance('environment', new Fallback(__DIR__));
    }

    /** @test */
    public function throwsWhenEnvironmentIsNotDefined()
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage('Name environment could not be resolved');

        $this->app->delete('environment');
        $this->app->loadConfiguration();
    }
}

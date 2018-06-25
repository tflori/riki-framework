<?php

namespace Riki\Test\Application;

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
    }

    /** @test */
    public function storesTheConfigInstance()
    {
        Application::loadConfig($this->app);

        self::assertInstanceOf(Config::class, $this->app->config);
        self::assertInstanceOf(Config::class, $this->app->get(ConfigExample::class));
    }

    /** @test */
    public function sticksWithCurrentConfig()
    {
        $this->app->instance('config', $config = new ConfigExample($this->app->environment));

        Application::loadConfig($this->app);

        self::assertSame($config, $this->app->config);
    }

    /** @test */
    public function loadsSerializedConfig()
    {
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/../Example/config.ser');

        Application::loadConfig($this->app);

        self::assertSame('Ie0g2aUbJi8=', $this->app->config->key);
    }

    /** @test */
    public function instantiatesNewConfig()
    {
        $this->environment->shouldReceive('canCacheConfig')->with()
            ->once()->andReturn(false);
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/../Example/config.ser');

        Application::loadConfig($this->app);

        self::assertNotSame('Ie0g2aUbJi8=', $this->app->config->key);
    }

    /** @test */
    public function setsTheCurrentEnvironment()
    {
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/../Example/config.ser');

        Application::loadConfig($this->app);

        self::assertSame($this->environment, $this->app->config->environment);
    }

    /** @test */
    public function throwsWhenConfigDoesNotExist()
    {
        $app = new Application(__DIR__, Fallback::class, 'UnknownClass');
        $app->instance('environment', new Fallback(__DIR__));

        self::expectException(Exception::class);
        self::expectExceptionMessage('Configuration not found');

        Application::loadConfig($app);
    }
}

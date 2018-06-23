<?php

namespace Riki\Test\Application;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Config;
use Riki\Environment;
use Mockery as m;
use Riki\Exception;
use Riki\Test\Application\Environment\Fallback;
use Riki\Test\Config\Example as ConfigExample;

class LoadConfigTest extends MockeryTestCase
{
    /** @var Example */
    protected $app;

    /** @var Environment|m\Mock */
    protected $environment;

    protected function setUp()
    {
        parent::setUp();
        $this->app = new Example(__DIR__);
        $this->environment = m::mock(Environment::class, [__DIR__])->makePartial();
        $this->app->instance('environment', $this->environment);
    }

    /** @test */
    public function storesTheConfigInstance()
    {
        $this->app->loadConfig($this->app);

        self::assertInstanceOf(Config::class, $this->app->config);
        self::assertInstanceOf(Config::class, $this->app->get(ConfigExample::class));
    }

    /** @test */
    public function sticksWithCurrentConfig()
    {
        $this->app->instance('config', $config = new ConfigExample($this->app->environment));

        $this->app->loadConfig($this->app);

        self::assertSame($config, $this->app->config);
    }

    /** @test */
    public function loadsSerializedConfig()
    {
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/config.ser');

        $this->app->loadConfig($this->app);

        self::assertSame('Ie0g2aUbJi8=', $this->app->config->key);
    }

    /** @test */
    public function setsTheCurrentEnvironment()
    {
        $this->environment->shouldReceive('getConfigCachePath')->with()
            ->once()->andReturn(__DIR__ . '/config.ser');

        $this->app->loadConfig($this->app);

        self::assertSame($this->environment, $this->app->config->environment);
    }

    /** @test */
    public function throwsWhenConfigDoesNotExist()
    {
        $app = new Example(__DIR__, Fallback::class, 'UnknownClass');
        $app->instance('environment', new Fallback(__DIR__));

        self::expectException(Exception::class);
        self::expectExceptionMessage('Configuration not found');

        $app->loadConfig($app);
    }
}
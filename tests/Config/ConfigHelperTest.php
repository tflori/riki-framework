<?php

namespace Riki\Test\Config;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Config;
use Riki\Exception;
use Riki\Test\Example\Application;

class ConfigHelperTest extends MockeryTestCase
{
    private $app;

    protected function tearDown(): void
    {
        if ($this->app) {
            $this->app->destroy();
        }

        parent::tearDown();
    }

    /** @test */
    public function throwsIfAppIsNotInitialized()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Application not initialized');

        config('test');
    }

    /** @test */
    public function returnsTheConfigObjectWithoutKey()
    {
        $this->app = new Application(new \Riki\Environment(__DIR__));

        self::assertInstanceOf(Config::class, config());
    }

    /** @test */
    public function returnsTheResponseFromConfigGet()
    {
        $this->app = new Application(new \Riki\Environment(__DIR__));
        $this->app->instance('config', $config = m::mock(Config::class)->makePartial());
        $config->shouldReceive('get')->with('test', 'fallback')->once()->andReturn('foo');

        self::assertSame('foo', config('test', 'fallback'));
    }
}

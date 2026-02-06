<?php

namespace Riki\Test\Environment;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Environment;
use Riki\Exception;
use Riki\Test\Example\Application;

class EnvHelperTest extends MockeryTestCase
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

        env('TEST_VAR');
    }

    /** @test */
    public function returnsTheEnvironmentObjectWithoutKey()
    {
        $this->app = new Application(new \Riki\Environment(__DIR__));

        self::assertInstanceOf(Environment::class, env());
    }

    /** @test */
    public function returnsTheResponseFromEnvironmentGet()
    {
        $env = m::mock(Environment::class)->makePartial();
        $env->__construct(__DIR__);
        $this->app = new Application($env);
        $env->shouldReceive('get')->with('TEST_VAR', 'fallback')
            ->once()->andReturn('test_value');

        self::assertSame('test_value', env('TEST_VAR', 'fallback'));
    }
}

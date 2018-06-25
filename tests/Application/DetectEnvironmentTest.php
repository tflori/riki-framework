<?php

namespace Riki\Test\Application;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Exception;
use Riki\Test\Example\Environment\Custom;
use Riki\Test\Example\Environment\Development;
use Riki\Test\Example\Environment\Fallback;
use Riki\Test\Example\Environment\ProductionCli;
use Riki\Test\Example\Application;

class DetectEnvironmentTest extends MockeryTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        putenv('APP_ENV=');
    }

    /** @test */
    public function createsTheFallbackEnvironment()
    {
        putenv('APP_ENV=strange');
        $app = new Application(__DIR__);

        Application::detectEnvironment($app);

        self::assertInstanceOf(Fallback::class, $app->environment);
    }

    /** @test */
    public function createsDevelopmentByDefault()
    {
        $app = new Application(__DIR__);

        Application::detectEnvironment($app);

        self::assertInstanceOf(Development::class, $app->environment);
    }

    /** @test */
    public function createsSpecificEnvironment()
    {
        putenv('APP_ENV=custom');
        $app = new Application(__DIR__);

        Application::detectEnvironment($app);

        self::assertInstanceOf(Custom::class, $app->environment);
    }

    /** @test */
    public function prefersCli()
    {
        putenv('APP_ENV=production');
        $app = new Application(__DIR__);

        Application::detectEnvironment($app);

        self::assertInstanceOf(ProductionCli::class, $app->environment);
    }

    /** @test */
    public function throwsWhenFallbackIsNotAvailable()
    {
        putenv('APP_ENV=strange');
        $app = new Application(__DIR__, 'UnknownClass');

        self::expectException(Exception::class);
        self::expectExceptionMessage('No environment found');

        Application::detectEnvironment($app);
    }

    /** @test */
    public function doesNotOverloadWhenLoaded()
    {
        $app = new Application(__DIR__);
        Application::detectEnvironment($app);
        $environment = $app->environment;
        putenv('APP_ENV=custom');

        Application::detectEnvironment($app);

        self::assertSame($environment, $app->environment);
    }
}

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
    protected function tearDown(): void
    {
        parent::tearDown();
        Application::app()->destroy();
        putenv('APP_ENV=');
    }

    /** @test */
    public function createsTheFallbackEnvironment()
    {
        putenv('APP_ENV=strange');
        $app = new Application(__DIR__);

        $app->detectEnvironment();

        self::assertInstanceOf(Fallback::class, $app->environment);
    }

    /** @test */
    public function createsDevelopmentByDefault()
    {
        $app = new Application(__DIR__);

        $app->detectEnvironment();

        self::assertInstanceOf(Development::class, $app->environment);
    }

    /** @test */
    public function createsSpecificEnvironment()
    {
        putenv('APP_ENV=custom');
        $app = new Application(__DIR__);

        $app->detectEnvironment();

        self::assertInstanceOf(Custom::class, $app->environment);
    }

    /** @test */
    public function prefersCli()
    {
        putenv('APP_ENV=production');
        $app = new Application(__DIR__);

        $app->detectEnvironment();

        self::assertInstanceOf(ProductionCli::class, $app->environment);
    }

    /** @test */
    public function throwsWhenFallbackIsNotAvailable()
    {
        putenv('APP_ENV=strange');

        self::expectException(Exception::class);
        self::expectExceptionMessage('No environment found');

        $app = new Application(__DIR__, 'UnknownClass');
    }
}

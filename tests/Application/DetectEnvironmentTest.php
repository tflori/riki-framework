<?php

namespace Riki\Test\Application;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Exception;
use Riki\Test\Application\Environment\Custom;
use Riki\Test\Application\Environment\Development;
use Riki\Test\Application\Environment\Fallback;
use Riki\Test\Application\Environment\ProductionCli;

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
        $app = new Example(__DIR__);

        $app->detectEnvironment($app);

        self::assertInstanceOf(Fallback::class, $app->environment);
    }

    /** @test */
    public function createsDevelopmentByDefault()
    {
        $app = new Example(__DIR__);

        $app->detectEnvironment($app);

        self::assertInstanceOf(Development::class, $app->environment);
    }

    /** @test */
    public function createsSpecificEnvironment()
    {
        putenv('APP_ENV=custom');
        $app = new Example(__DIR__);

        $app->detectEnvironment($app);

        self::assertInstanceOf(Custom::class, $app->environment);
    }

    /** @test */
    public function prefersCli()
    {
        putenv('APP_ENV=production');
        $app = new Example(__DIR__);

        $app->detectEnvironment($app);

        self::assertInstanceOf(ProductionCli::class, $app->environment);
    }

    /** @test */
    public function throwsWhenFallbackIsNotAvailable()
    {
        putenv('APP_ENV=strange');
        $app = new Example(__DIR__, 'UnknownClass');

        self::expectException(Exception::class);
        self::expectExceptionMessage('No environment found');

        $app->detectEnvironment($app);
    }

    /** @test */
    public function doesNotOverloadWhenLoaded()
    {
        $app = new Example(__DIR__);
        $app->detectEnvironment($app);
        $environment = $app->environment;
        putenv('APP_ENV=custom');

        $app->detectEnvironment($app);

        self::assertSame($environment, $app->environment);
    }
}

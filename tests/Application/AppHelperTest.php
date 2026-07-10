<?php

namespace Riki\Test\Application;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Application;
use Riki\Environment;
use Riki\Exception;
use Riki\Test\Example\Application as TestApplication;

class AppHelperTest extends MockeryTestCase
{
    private ?TestApplication $app = null;

    protected function tearDown(): void
    {
        $this->app?->destroy();
        parent::tearDown();
    }

    /** @test */
    public function throwsIfAppIsNotInitialized()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Application not initialized');

        app('some.service');
    }

    /** @test */
    public function returnsTheApplicationInstanceWithoutArgument()
    {
        $this->app = new TestApplication(new Environment(__DIR__));

        self::assertInstanceOf(Application::class, app());
    }

    /** @test */
    public function returnsNamedServiceWhenKeyDoesNotMatchAClass()
    {
        $this->app = new TestApplication(new Environment(__DIR__));
        $this->app->instance('some.service', $service = new \stdClass());

        self::assertSame($service, app('some.service'));
    }

    /** @test */
    public function makesClassInstanceWhenKeyIsAnExistingClass()
    {
        $this->app = new TestApplication(new Environment(__DIR__));

        $result = app(\stdClass::class);

        self::assertInstanceOf(\stdClass::class, $result);
    }

    /** @test */
    public function passesAdditionalArgsToGet()
    {
        $this->app = new TestApplication(new Environment(__DIR__));
        $this->app->add('greeter', fn($name) => "hello $name");

        self::assertSame('hello world', app('greeter', 'world'));
    }
}
<?php

namespace Riki\Test\Application;

use DependencyInjector\DI;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Exception;
use Riki\Test\Example\Application;
use Mockery as m;

class ConstructTest extends MockeryTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        Application::app()->destroy();
    }

    /** @test */
    public function storesItselfInTheContainer()
    {
        $app = new Application(__DIR__ . '/..');
        $result1 = $app->get('app');
        $result2 = $app->get(\Riki\Application::class);

        self::assertSame($app, $result1);
        self::assertSame($app, $result2);
    }

    /** @test */
    public function definesItSelfForStaticAccess()
    {
        $app = new Application(__DIR__ . '/..');
        $result = DI::getContainer();

        self::assertSame($app, $result);
    }

    /** @test */
    public function detectsEnvironmentInConstructor()
    {
        $app = m::mock(Application::class)->makePartial();
        $app->shouldReceive('detectEnvironment')->with()
            ->once()->passthru();

        $app->__construct(__DIR__ . '/..');
    }

    /** @test */
    public function loadsConfigurationInConstructor()
    {
        $app = m::mock(Application::class)->makePartial();
        $app->shouldReceive('loadConfiguration')->with()
            ->once()->passthru();

        $app->__construct(__DIR__ . '/..');
    }

    /** @test */
    public function allowOnlyOneInstance()
    {
        $app = new Application(__DIR__ . '/..');

        self::expectException(Exception::class);
        self::expectExceptionMessage('There can only be one application at the same time');

        new Application(__DIR__ . '/..');
    }
}

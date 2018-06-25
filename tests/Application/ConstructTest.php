<?php

namespace Riki\Test\Application;

use DependencyInjector\DI;
use PHPUnit\Framework\TestCase;
use Riki\Test\Example\Application;

class ConstructTest extends TestCase
{
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
    public function addsTwoBootstrappers()
    {
        $app = new Application(__DIR__ . '/..');

        $bootstrappers = $app->getBootstrappers();

        self::assertCount(2, $bootstrappers);
        self::assertEquals([
            [Application::class, 'detectEnvironment'],
            [Application::class, 'loadConfig'],
        ], $bootstrappers);
    }
}

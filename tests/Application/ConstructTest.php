<?php

namespace Riki\Test\Application;

use DependencyInjector\DI;
use PHPUnit\Framework\TestCase;
use Riki\Application;

class ConstructTest extends TestCase
{
    /** @test */
    public function storesItselfInTheContainer()
    {
        $app = new Example(__DIR__ . '/..');
        $result1 = $app->get('app');
        $result2 = $app->get(Application::class);

        self::assertSame($app, $result1);
        self::assertSame($app, $result2);
    }

    /** @test */
    public function definesItSelfForStaticAccess()
    {
        $app = new Example(__DIR__ . '/..');
        $result = DI::getContainer();

        self::assertSame($app, $result);
    }

    /** @test */
    public function addsTwoBootstrappers()
    {
        $app = new Example(__DIR__ . '/..');

        $bootstrappers = $app->getBootstrappers();

        self::assertCount(2, $bootstrappers);
        self::assertEquals([
            [$app, 'detectEnvironment'],
            [$app, 'loadConfig'],
        ], $bootstrappers);
    }
}

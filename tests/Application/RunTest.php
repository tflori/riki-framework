<?php

namespace Riki\Test\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Exception;
use Riki\Kernel;
use Riki\Test\Example\Application;
use Riki\Test\Example\OptionLoader;

class RunTest extends MockeryTestCase
{
    /** @var Kernel|m\Mock */
    protected $kernel;

    /** @var Application */
    protected $app;

    public function provideBootstrappers()
    {
        require_once __DIR__ . '/../Example/helper.php';
        return [
            ['registerRoutes', 'Bootstrapper registerRoutes'],
            [function () {
                return 0;
            }, 'Unknown bootstrapper'],
            [new OptionLoader(), 'Bootstrapper Riki\Test\Example\OptionLoader'],
            [[new OptionLoader(), 'load'], 'Bootstrapper Riki\Test\Example\OptionLoader::load'],
            [[OptionLoader::class, 'loadStatic'], 'Bootstrapper Riki\Test\Example\OptionLoader::loadStatic'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->app = new Application(__DIR__);
        $kernel = $this->kernel = m::mock(Kernel::class);
        $kernel->shouldReceive('handle')->andReturn(0)->byDefault();
        $kernel->shouldReceive('getBootstrappers')->andReturn([])->byDefault();
    }

    /** @test */
    public function executesTheBootstrappers()
    {
        $calls = 0;
        $this->kernel->shouldReceive('getBootstrappers')->with()
            ->once()->andReturn([function () use (&$calls) {
                $calls++;
                return true;
            }]);

        $this->app->run($this->kernel);

        self::assertSame(1, $calls);
    }

    /** @test */
    public function callsHandleWithAdditionalArgs()
    {
        $this->kernel->shouldReceive('handle')->with($this->app, 'foo', 'bar')
            ->once()->andReturn(42);

        $result = $this->app->run($this->kernel, 'foo', 'bar');

        self::assertSame(42, $result);
    }

    /** @dataProvider provideBootstrappers
     * @param $bootstrapper
     * @param $name
     * @test */
    public function throwsWhenBootstrapperDoesNotReturnATruthyValue($bootstrapper, $name)
    {
        $this->kernel->shouldReceive('getBootstrappers')->with()
            ->once()->andReturn([$bootstrapper]);

        self::expectException(Exception::class);
        self::expectExceptionMessage($name . ' failed for unknown reason');

        $this->app->run($this->kernel);
    }
}

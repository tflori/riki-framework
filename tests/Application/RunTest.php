<?php

namespace Riki\Test\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
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

    protected function tearDown()
    {
        parent::tearDown();
        Application::app()->destroy();
    }


    /** @test */
    public function callsHandleWithAdditionalArgs()
    {
        $this->kernel->shouldReceive('handle')->with('foo', 'bar')
            ->once()->andReturn(42);

        $result = $this->app->run($this->kernel, 'foo', 'bar');

        self::assertSame(42, $result);
    }
}

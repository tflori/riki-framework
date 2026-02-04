<?php

namespace Riki\Test\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Environment;
use Riki\Kernel;
use Riki\Test\Example\Application;

class RunTest extends MockeryTestCase
{
    /** @var Kernel|m\Mock */
    protected $kernel;

    /** @var Application */
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(new Environment(__DIR__));
        $kernel = $this->kernel = m::mock(Kernel::class);
        $kernel->shouldReceive('handle')->andReturn(0)->byDefault();
    }

    protected function tearDown(): void
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

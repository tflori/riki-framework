<?php

namespace Riki\Test\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Config;
use Riki\Environment;
use Riki\Test\Example\Application;
use Riki\Test\Example\Config as ConfigExample;

class LoadConfigTest extends MockeryTestCase
{
    /** @var Application */
    protected $app;

    /** @var Environment|m\Mock */
    protected $environment;

    protected function setUp(): void
    {
        $this->environment = m::mock(Environment::class)->makePartial();
        $this->environment->__construct(__DIR__);
        $this->app = m::mock(Application::class)->makePartial();
    }

    protected function tearDown(): void
    {
        if (file_exists('/tmp/config.ser')) {
            unlink('/tmp/config.ser');
        }
        Application::app()->destroy();
    }

    protected function callProtectedMethod(object $object, string $method, array $args = [])
    {
        $reflection = new \ReflectionMethod($object, $method);
        /** @noinspection PhpExpressionResultUnusedInspection */
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($object, $args);
    }

    protected function makeTempDir(string $prefix): string
    {
        $base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        $path = $base . DIRECTORY_SEPARATOR . $prefix . '-' . uniqid('', true);
        mkdir($path, 0777, true);
        return $path;
    }

    /** @test */
    public function storesTheConfigInstance()
    {
        $this->app->__construct($this->environment, ['configClass' => ConfigExample::class]);

        self::assertInstanceOf(ConfigExample::class, $this->app->config);
        self::assertInstanceOf(ConfigExample::class, $this->app->get(Config::class));
    }

    /** @test */
    public function loadsSerializedConfig()
    {
        $this->app->__construct($this->environment, ['cachePath' => __DIR__ . '/../Example/config.ser']);

        self::assertSame('Ie0g2aUbJi8=', $this->app->config->key);
    }

    /** @test */
    public function locateConfigFilesReturnsEmptyArrayWhenPathIsNotDirectory()
    {
        $this->app->__construct($this->environment);

        $result = $this->callProtectedMethod($this->app, 'locateConfigFiles', ['/path/does/not/exist']);

        self::assertSame([], $result);
    }

    /** @test */
    public function generateConfigurationLoadsEnvironmentAndConfigFiles()
    {
        $root = $this->makeTempDir('riki-config');
        $configDir = $root . DIRECTORY_SEPARATOR . 'config';
        mkdir($configDir, 0777, true);

        $envFile = $root . DIRECTORY_SEPARATOR . '.env';
        file_put_contents(
            $envFile,
            <<<'ENV'
APP_ENV=
FOO=from-env
ENV
        );

        $configFile = $configDir . DIRECTORY_SEPARATOR . 'test.php';
        file_put_contents(
            $configFile,
            <<<'PHP'
<?php
return ['value' => env('FOO', 'default')];
PHP
        );

        $environment = new Environment($root);
        $this->app->__construct($environment);

        $config = $this->callProtectedMethod($this->app, 'generateConfiguration');

        self::assertInstanceOf(Config::class, $config);
        self::assertSame('from-env', $config->get('test.value'));
    }

    /** @test */
    public function baseGenerateConfigurationIsUsedByDefault()
    {
        $root = $this->makeTempDir('riki-config-base');
        $configDir = $root . DIRECTORY_SEPARATOR . 'config';
        mkdir($configDir, 0777, true);

        $envFile = $root . DIRECTORY_SEPARATOR . '.env';
        file_put_contents(
            $envFile,
            <<<'ENV'
APP_ENV=
FOO=base-env
ENV
        );

        $configFile = $configDir . DIRECTORY_SEPARATOR . 'base.php';
        file_put_contents(
            $configFile,
            <<<'PHP'
<?php
return ['value' => env('FOO', 'default')];
PHP
        );

        $environment = new Environment($root);
        $app = new class ($environment) extends \Riki\Application {
        };

        self::assertSame('base-env', $app->config->get('base.value'));
    }
}

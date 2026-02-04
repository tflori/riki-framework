<?php

namespace Riki\Test\Application;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Config;
use Riki\Environment;
use Riki\Test\Example\Application;

class RebuildConfigTest extends MockeryTestCase
{
    /** @var Application|null */
    protected $app;

    /** @var array */
    protected $tempPaths = [];

    protected function tearDown(): void
    {
        if ($this->app) {
            $this->app->destroy();
        }

        foreach ($this->tempPaths as $path) {
            $this->removePath($path);
        }
    }

    protected function makeTempDir(string $prefix): string
    {
        $base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        $path = $base . DIRECTORY_SEPARATOR . $prefix . '-' . uniqid('', true);
        mkdir($path, 0777, true);
        $this->tempPaths[] = $path;
        return $path;
    }

    protected function removePath(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_dir($path) && !is_link($path)) {
            $items = scandir($path);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $this->removePath($path . DIRECTORY_SEPARATOR . $item);
                }
            }
            rmdir($path);
            return;
        }

        unlink($path);
    }

    /** @test */
    public function rebuildsTheConfigurationCacheFile()
    {
        $root = $this->makeTempDir('riki-config-rebuild');
        $configDir = $root . DIRECTORY_SEPARATOR . 'config';
        mkdir($configDir, 0777, true);
        file_put_contents(
            $configDir . DIRECTORY_SEPARATOR . 'test.php',
            <<<'PHP'
<?php
return ['value' => 'cached'];
PHP
        );

        $cachePath = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.dat';
        $environment = new Environment($root);
        $this->app = new Application($environment, ['cachePath' => $cachePath]);

        $result = $this->app->rebuildConfigurationCache();

        self::assertTrue($result);
        self::assertFileExists($cachePath);

        $config = unserialize(file_get_contents($cachePath));

        self::assertInstanceOf(Config::class, $config);
        self::assertSame('cached', $config->get('test.value'));
    }

    /** @test */
    public function returnsFalseWhenCacheDirectoryCannotBeCreated()
    {
        $root = $this->makeTempDir('riki-config-rebuild-fail');
        $configDir = $root . DIRECTORY_SEPARATOR . 'config';
        mkdir($configDir, 0777, true);
        file_put_contents(
            $configDir . DIRECTORY_SEPARATOR . 'test.php',
            <<<'PHP'
<?php
return ['value' => 'nope'];
PHP
        );

        $blocker = $root . DIRECTORY_SEPARATOR . 'blocked';
        file_put_contents($blocker, 'not-a-directory');

        $environment = new Environment($root);
        $this->app = new Application($environment, ['cachePath' => $blocker . DIRECTORY_SEPARATOR . 'config.dat']);

        $result = $this->app->rebuildConfigurationCache();

        self::assertFalse($result);
    }
}

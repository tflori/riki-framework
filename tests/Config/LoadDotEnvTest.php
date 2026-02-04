<?php

namespace Riki\Test\Config;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Riki\Environment;
use Riki\Config;
use Riki\Test\Example\Application;

class LoadDotEnvTest extends MockeryTestCase
{
    private $tempDir;
    private $envFilePath;
    private $app;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/riki_config_dotenv_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);

        // Create a sample .env file
        $this->envFilePath = $this->tempDir . '/.env';
        $envContent = <<<'ENV'
DB_HOST=localhost
DB_PORT=3306
APP_NAME=TestApp
APP_DEBUG=true
ENV;
        file_put_contents($this->envFilePath, $envContent . "\n");
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory
        $this->removeDirectoryRecursively($this->tempDir);

        // Clear environment variables set during tests
        putenv('DB_HOST');
        putenv('DB_PORT');
        putenv('APP_NAME');
        putenv('APP_DEBUG');

        if ($this->app) {
            $this->app->destroy();
        }
        parent::tearDown();
    }

    private function removeDirectoryRecursively($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectoryRecursively($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testConfigCanAccessEnvironmentVariablesAfterLoading()
    {
        $environment = new Environment($this->tempDir);
        $this->app = new Application($environment);

        // Load environment first
        $environment->loadEnvironment();

        // Create a temporary config file that uses env() helper
        $configFile = $this->tempDir . '/test_db_config.php';
        $configContent = <<<'PHP'
<?php
return ['database' => ['host' => env('DB_HOST', 'default_host')]];
PHP;
        file_put_contents($configFile, $configContent . "\n");

        // Load config from file using fromFiles method
        $config = Config::fromFiles(['db' => $configFile], $environment);

        $this->assertEquals('localhost', $config['db']['database']['host']);
    }

    public function testConfigResolvesMultipleEnvironmentVariables()
    {
        $environment = new Environment($this->tempDir);
        $this->app = new Application($environment);
        $environment->loadEnvironment();

        // Create a temporary config file that uses env() helper
        $configFile = $this->tempDir . '/test_multi_config.php';
        $configContent = <<<'PHP'
<?php
return [
    'database' => [
        'host' => env('DB_HOST', 'default_host'),
        'port' => env('DB_PORT', 3307)
    ],
    'app' => [
        'name' => env('APP_NAME', 'DefaultApp'),
        'debug' => env('APP_DEBUG', false)
    ]
];
PHP;
        file_put_contents($configFile, $configContent);

        // Load config from file using fromFiles method
        $config = Config::fromFiles(['multi' => $configFile], $environment);

        $this->assertEquals('localhost', $config['multi']['database']['host']);
        $this->assertEquals('3306', $config['multi']['database']['port']);
        $this->assertEquals('TestApp', $config['multi']['app']['name']);
        $this->assertEquals('true', $config['multi']['app']['debug']) || $this->assertTrue($config['multi']['app']['debug']);
    }

    public function testConfigHandlesMissingEnvironmentVariables()
    {
        $environment = new Environment($this->tempDir);
        $environment->loadEnvironment();
        $this->app = new Application($environment);

        // Create a temporary config file that uses env() helper with a missing variable
        $configFile = $this->tempDir . '/test_missing_config.php';
        $configContent = <<<'PHP'
<?php
return ['missing_var' => env('MISSING_VAR', 'default_value')];
PHP;
        file_put_contents($configFile, $configContent . "\n");

        // Load config from file using fromFiles method
        $config = Config::fromFiles(['missing' => $configFile], $environment);

        // Missing variables should return the default value
        $this->assertEquals('default_value', $config['missing']['missing_var']);
    }
}

<?php

namespace Riki\Test\Environment;

use PHPUnit\Framework\TestCase;
use Riki\Test\Example\Environment;

class EnvironmentTest extends TestCase
{
    private $tempDir;
    private $envFilePath;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/riki_env_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
        
        // Create a sample .env file
        $this->envFilePath = $this->tempDir . '/.env';
        file_put_contents($this->envFilePath, "TEST_VAR=test_value\nANOTHER_VAR=another_value\n");
    }

    protected function tearDown(): void
    {
        // Clear environment variables set during tests
        putenv('TEST_VAR');
        putenv('ANOTHER_VAR');
        putenv('APP_ENV');

        // Clean up temporary directory
        $this->removeDirectoryRecursively($this->tempDir);

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

    public function testEnvironmentVariablesNotLoadedByDefault()
    {
        $environment = new Environment($this->tempDir);
        
        // Variables should not be accessible before loading
        $this->assertFalse(isset($environment['TEST_VAR']));
        $this->assertFalse(isset($environment['ANOTHER_VAR']));
        $this->assertFalse($environment->isEnvLoaded());
    }

    public function testLoadEnvironmentLoadsVariables()
    {
        $environment = new Environment($this->tempDir);
        
        // Load the environment
        $environment->loadEnvironment();
        
        // Variables should now be accessible
        $this->assertTrue(isset($environment['TEST_VAR']));
        $this->assertTrue(isset($environment['ANOTHER_VAR']));
        $this->assertEquals('test_value', $environment['TEST_VAR']);
        $this->assertEquals('another_value', $environment['ANOTHER_VAR']);
        $this->assertTrue($environment->isEnvLoaded());
    }

    public function testLoadEnvironmentDoesNotReloadOnceLoaded()
    {
        $environment = new Environment($this->tempDir);

        // Load the environment
        $environment->loadEnvironment();
        $firstLoadValue = $environment['TEST_VAR'] ?? null;

        // Modify the .env file after first load
        $originalContent = file_get_contents($this->envFilePath);
        file_put_contents($this->envFilePath, "MODIFIED_VAR=modified_value\n");

        // Try to load again - should not reload
        $environment->loadEnvironment();
        $secondLoadValue = $environment['TEST_VAR'] ?? null;
        $modifiedValue = $environment['MODIFIED_VAR'] ?? null;

        // Values should remain from first load, and new values shouldn't be loaded
        $this->assertEquals('test_value', $firstLoadValue);
        $this->assertEquals('test_value', $secondLoadValue);
        $this->assertNull($modifiedValue); // MODIFIED_VAR should not be loaded after first load

        // Restore original content
        file_put_contents($this->envFilePath, $originalContent);
    }

    public function testLoadEnvironmentWithAppEnv()
    {
        // Set APP_ENV to trigger loading of .env.{APP_ENV}
        putenv('APP_ENV=testing');
        
        // Create a .env.testing file
        $envTestingPath = $this->tempDir . '/.env.testing';
        file_put_contents($envTestingPath, "TESTING_VAR=testing_value\n");
        
        $environment = new Environment($this->tempDir);
        $environment->loadEnvironment();
        
        // Should load from .env.testing first
        $this->assertTrue(isset($environment['TESTING_VAR']));
        $this->assertEquals('testing_value', $environment['TESTING_VAR']);
        
        // Clean up
        unlink($envTestingPath);
    }

    public function testLoadEnvironmentWithSearch()
    {
        // Create a parent directory structure
        $parentDir = $this->tempDir . '/parent';
        $childDir = $parentDir . '/child';
        mkdir($parentDir, 0755, true);
        mkdir($childDir, 0755, true);
        
        // Put .env file in parent directory
        $parentEnvPath = $parentDir . '/.env';
        file_put_contents($parentEnvPath, "PARENT_VAR=parent_value\n");
        
        // Create environment in child directory
        $environment = new Environment($childDir);
        $environment->loadEnvironment(true); // Enable search
        
        // Should find and load .env from parent directory
        $this->assertTrue(isset($environment['PARENT_VAR']));
        $this->assertEquals('parent_value', $environment['PARENT_VAR']);
        
        // Clean up
        unlink($parentEnvPath);
        rmdir($childDir);
        rmdir($parentDir);
    }

    public function testLoadEnvironmentWithoutSearchDoesNotFindParent()
    {
        // Create a parent directory structure
        $parentDir = $this->tempDir . '/parent';
        $childDir = $parentDir . '/child';
        mkdir($parentDir, 0755, true);
        mkdir($childDir, 0755, true);
        
        // Put .env file in parent directory
        $parentEnvPath = $parentDir . '/.env';
        file_put_contents($parentEnvPath, "PARENT_VAR=parent_value\n");
        
        // Create environment in child directory
        $environment = new Environment($childDir);
        $environment->loadEnvironment(false); // Disable search
        
        // Should NOT find .env from parent directory
        $this->assertFalse(isset($environment['PARENT_VAR']));
        
        // Clean up
        unlink($parentEnvPath);
        rmdir($childDir);
        rmdir($parentDir);
    }

    public function testLoadEnvironmentHandlesNonExistentFileGracefully()
    {
        // Create environment in directory without .env file
        $emptyDir = $this->tempDir . '/empty';
        mkdir($emptyDir, 0755, true);
        
        $environment = new Environment($emptyDir);
        $environment->loadEnvironment();
        
        // Should not crash and should remain unloaded
        $this->assertFalse($environment->isEnvLoaded());
        
        // Clean up
        rmdir($emptyDir);
    }

    public function testOffsetSetDoesNothing()
    {
        $environment = new Environment($this->tempDir);
        
        // Try to set a value - should not change anything
        $environment['NEW_VAR'] = 'new_value';
        
        // Value should not be set
        $this->assertFalse(isset($environment['NEW_VAR']));
    }

    public function testOffsetUnsetDoesNothing()
    {
        $environment = new Environment($this->tempDir);
        $environment->loadEnvironment();
        
        // Verify variable exists
        $this->assertTrue(isset($environment['TEST_VAR']));
        
        // Try to unset - should not change anything
        unset($environment['TEST_VAR']);
        
        // Value should still exist
        $this->assertTrue(isset($environment['TEST_VAR']));
    }

    public function testPathMethod()
    {
        $environment = new Environment($this->tempDir);
        
        $expectedPath = $this->tempDir . DIRECTORY_SEPARATOR . 'some' . DIRECTORY_SEPARATOR . 'path';
        $actualPath = $environment->path('some', 'path');
        
        $this->assertEquals($expectedPath, $actualPath);
    }

    public function testResourcePathMethod()
    {
        $environment = new Environment($this->tempDir);
        
        $expectedPath = $environment->resourcePath('assets', 'css');
        $expectedBase = $this->tempDir . DIRECTORY_SEPARATOR . 'resources';
        $expectedFull = $expectedBase . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css';
        
        $this->assertEquals($expectedFull, $expectedPath);
    }

    public function testStoragePathMethod()
    {
        $environment = new Environment($this->tempDir);
        
        $expectedPath = $environment->storagePath('logs', 'app.log');
        $expectedBase = $this->tempDir . DIRECTORY_SEPARATOR . 'storage';
        $expectedFull = $expectedBase . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log';
        
        $this->assertEquals($expectedFull, $expectedPath);
    }

    public function testCachePathMethod()
    {
        $environment = new Environment($this->tempDir);
        
        $expectedPath = $environment->cachePath('config.cache');
        $expectedBase = $this->tempDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
        $expectedFull = $expectedBase . DIRECTORY_SEPARATOR . 'config.cache';
        
        $this->assertEquals($expectedFull, $expectedPath);
    }

    public function testConfigPathMethod()
    {
        $environment = new Environment($this->tempDir);
        
        $expectedPath = $environment->configPath('app.php');
        $expectedBase = $this->tempDir . DIRECTORY_SEPARATOR . 'config';
        $expectedFull = $expectedBase . DIRECTORY_SEPARATOR . 'app.php';
        
        $this->assertEquals($expectedFull, $expectedPath);
    }
}

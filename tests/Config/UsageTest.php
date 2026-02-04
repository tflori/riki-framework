<?php

namespace Riki\Test\Config;

use PHPUnit\Framework\TestCase;
use Riki\Config;

class UsageTest extends TestCase
{
    /** @test */
    public function getReturnsNestedValue()
    {
        $config = new Config([
            'database' => [
                'host' => 'localhost',
            ],
        ]);

        self::assertSame('localhost', $config->get('database.host'));
    }

    /** @test */
    public function getReturnsDefaultWhenKeyIsMissing()
    {
        $config = new Config([]);

        self::assertSame('fallback', $config->get('missing.key', 'fallback'));
    }

    /** @test */
    public function getReturnsConfigForAssociativeArray()
    {
        $config = new Config([
            'database' => [
                'host' => 'localhost',
            ],
        ]);

        $database = $config->get('database');

        self::assertInstanceOf(Config::class, $database);
        self::assertSame('localhost', $database->get('host'));
    }

    /** @test */
    public function getReturnsConfigForAssociativeArrayFallback()
    {
        $config = new Config([]);

        $fallback = $config->get('database', ['host' => 'localhost']);

        self::assertInstanceOf(Config::class, $fallback);
        self::assertSame('localhost', $fallback->get('host'));
    }

    /** @test */
    public function setCreatesNestedStructure()
    {
        $config = new Config([]);

        $config->set('database.host', 'localhost');

        self::assertSame('localhost', $config->get('database.host'));
    }

    /** @test */
    public function setOverwritesExistingValue()
    {
        $config = new Config([
            'app' => [
                'debug' => false,
            ],
        ]);

        $config->set('app.debug', true);

        self::assertSame(true, $config->get('app.debug'));
    }

    /** @test */
    public function setAllowsAssociativeArrayValueReturnedAsConfig()
    {
        $config = new Config([]);

        $config->set('services', [
            'mailer' => [
                'host' => 'smtp.local',
            ],
        ]);

        $services = $config->get('services');

        self::assertInstanceOf(Config::class, $services);
        self::assertSame('smtp.local', $services->get('mailer.host'));
    }

    /** @test */
    public function pushAppendsToArray()
    {
        $config = new Config([
            'tags' => ['one'],
        ]);

        $config->push('tags', 'two');

        self::assertSame(['one', 'two'], $config->get('tags'));
    }

    /** @test */
    public function pushCreatesArrayWhenMissing()
    {
        $config = new Config([]);

        $config->push('tags', 'first');

        self::assertSame(['first'], $config->get('tags'));
    }

    /** @test */
    public function pushRejectsAssociativeArrayTargets()
    {
        $config = new Config([
            'settings' => [
                'mode' => 'on',
            ],
        ]);

        $this->expectException(\TypeError::class);

        $config->push('settings', 'extra');
    }

    /** @test */
    public function arrayAccessExistsChecksTopLevelKeys()
    {
        $config = new Config([
            'app' => [
                'name' => 'TestApp',
            ],
        ]);

        self::assertTrue(isset($config['app']));
        self::assertFalse(isset($config['missing']));
    }

    /** @test */
    public function arrayAccessGetReturnsConfigForAssociativeArray()
    {
        $config = new Config([
            'app' => [
                'name' => 'TestApp',
            ],
        ]);

        $app = $config['app'];

        self::assertInstanceOf(Config::class, $app);
        self::assertSame('TestApp', $app->get('name'));
    }

    /** @test */
    public function arrayAccessSetStoresValues()
    {
        $config = new Config([]);

        $config['app'] = [
            'name' => 'TestApp',
        ];

        self::assertSame('TestApp', $config->get('app.name'));
    }

    /** @test */
    public function arrayAccessUnsetRemovesValues()
    {
        $config = new Config([
            'app' => [
                'name' => 'TestApp',
            ],
        ]);

        unset($config['app']);

        self::assertSame('fallback', $config->get('app', 'fallback'));
    }
}

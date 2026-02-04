# ríki

[![.github/workflows/push.yml](https://github.com/tflori/riki-framework/actions/workflows/push.yml/badge.svg)](https://github.com/tflori/riki-framework/actions/workflows/push.yml)
[![Code Coverage](https://qlty.sh/gh/tflori/projects/riki-framework/coverage.svg)](https://qlty.sh/gh/tflori/projects/riki-framework)
[![Maintainability](https://qlty.sh/gh/tflori/projects/riki-framework/maintainability.svg)](https://qlty.sh/gh/tflori/projects/riki-framework)
[![Latest Stable Version](https://poser.pugx.org/tflori/riki-framework/v/stable.svg)](https://packagist.org/packages/tflori/riki-framework)
[![Total Downloads](https://poser.pugx.org/tflori/riki-framework/downloads.svg)](https://packagist.org/packages/tflori/riki-framework)
[![License](https://poser.pugx.org/tflori/riki-framework/license.svg)](https://packagist.org/packages/tflori/riki-framework)

ríki is a minimalistic framework that focuses on **bootstrapping**, **environment handling** and **configuration loading**.
It does not provide controllers, routing, or a fixed application architecture. You bring the structure — ríki keeps the
foundation small and explicit.

## Install

```console
$ composer require tflori/riki-framework
```

## Recommended project structure

There is no required structure, but the following layout is recommended because it matches how `Riki\Environment`
resolves paths by default:

```
your-app/
 bootstrap.php # creates and returns the Application instance
 .env # environment configuration (defaults)
 .env.production # optional, depends on APP_ENV
 config/ # PHP config files returning arrays
 resources/ # templates, translations, assets, ...
 storage/ # cache, logs, runtime files
 public/
  index.php # web entrypoint
```

## Bootstrapping (`bootstrap.php`)

`bootstrap.php` is intentionally small:
- it loads Composer autoloading
- it creates an `Environment`
- it creates your application and returns it

Example:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$environment = new \Riki\Environment(realpath(__DIR__));
$application = new \App\Application($environment);

return $application;
```

## Environment

The `Riki\Environment` class represents the install root and the filesystem conventions of your app.
It provides path helpers (config/resources/storage) and lazily loads environment variables from `.env` files.

Environment variables are loaded automatically on first config load. The environment file is chosen by `APP_ENV`, which
must be set from the process environment (shell, php-fpm, nginx, systemd, CI, etc.). Process environment always
overrides values from `.env`.

Example:

```
APP_ENV=production
```

This will load `.env.production` if it exists, otherwise it falls back to `.env`.

Common usage:

```php
$environment = new \Riki\Environment(realpath(__DIR__));
$environment->loadEnvironment();

$cacheDir = $environment->cachePath();
$value = $environment->get('APP_ENV', 'local');
```

## Config

`Riki\Config` is a small wrapper around nested arrays. It loads your `config/*.php` files and provides dot-notation
lookups that return sub-config objects when the value is associative.

Configuration is done with PHP files in `config/` that return arrays. There are no environment-specific config files;
use `.env` and `APP_ENV` for that.

Use the `env($key, $default)` helper in config files:

```php
<?php

return [
    'name' => 'My App',
    'log' => [
        'level' => env('LOG_LEVEL', 'WARNING'),
    ],
];
```

Reading config values:

```php
$config = \Riki\Application::config();
$name = $config->get('name');
$level = $config->get('log.level', 'WARNING');
```

Mutating config values at runtime (useful for tests or overrides):

```php
$config->set('log.level', 'INFO');
$config->push('features.enabled', 'new-feature');
```

## Application

`Riki\Application` is your DI container and the central registry for the environment and configuration.
It ensures there is only one live application at a time and exposes static accessors for convenience
(`Application::environment()`, `Application::config()`, and any container entry).

Typical usage is to extend it and register dependencies:

```php
class App extends \Riki\Application
{
    protected function initDependencies()
    {
        $this->instance('logger', new \Psr\Log\NullLogger());
        $this->alias('logger', \Psr\Log\LoggerInterface::class);
    }
}
```

Then construct it in `bootstrap.php` and return it:

```php
$environment = new \Riki\Environment(realpath(__DIR__));
return new \App\App($environment);
```

## Kernel

`Riki\Kernel` represents a single entrypoint for your application (web, CLI, jobs, etc.).
It is intentionally tiny: pass the application in, and implement `handle()` with whatever boot logic you need.

Because `Application::run()` simply calls `handle()`, you decide when to run bootstrappers, dispatch requests, or
short-circuit early.

Example:

```php
class HttpKernel extends \Riki\Kernel
{
    public function handle()
    {
        $router = \Riki\Application::app()->get('router');
        return $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }
}
```

## Helper function

The `env()` helper reads values from the current environment object and can be used inside config files:

```php
$level = env('LOG_LEVEL', 'WARNING');
```

## Exception

`Riki\Exception` is a dedicated base exception for the framework. You can catch it to separate framework-level failures
from your own exceptions if you want to handle them differently.

## Entrypoint

Entrypoint (for example `public/index.php`):

```php
<?php

class MyKernel extends \Riki\Kernel
{
    public function handle()
    {
        echo 'Hello world!' . PHP_EOL;
        return 0;
    }
}
```

```php
<?php

$app = require __DIR__ . '/../bootstrap.php';
$ret = $app->run(new MyKernel($app));
if ($ret > 0) {
    exit($ret);
}
```

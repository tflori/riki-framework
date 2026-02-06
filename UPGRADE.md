# Upgrade Guides for Riki Framework

## v1.x to v2.0

This upgrade shifts from environment-specific PHP classes to a **single Environment instance** driven by `.env` **variables** and **file-based configuration**. This simplifies bootstrapping and makes configuration more flexible.

### 1. Unified Bootstrapping

The old approach of using different entry points and environment classes is replaced by a single `bootstrap.php` file.

**Before:** You had environment classes like `App\Environment\Development` and `App\Environment\Production`.
**Now:** These classes are removed. All environment-specific settings are handled by `.env` variables, which are loaded automatically by the framework.

**Action:**

1.  **Create `bootstrap.php`:**
    Create a new `bootstrap.php` file in your project root. This file will now be the single source of truth for initializing your application.

    ```php
    use App\Application;
    use App\Environment;

    require_once __DIR__ . '/vendor/autoload.php';

    $environment = new Environment(__DIR__);
    $app = new Application($environment);

    return $app;
    ```

2.  **Update Entry Points:**
    Modify your `public/index.php` and `bin/riki` to use the new bootstrap file.

    ```php
    use App\Any\Kernel;

    $app = require __DIR__ . '/../bootstrap.php';
    $kernel = new Kernel($app);
    // ...
    ```

### 2. Configuration Decoupling

Configuration is no longer managed in `App\Config.php` or `App\Environment` subclasses. Instead, it's loaded from `.php` files in the `config/` directory.

**Action:**

1.  **Delete old files:**
    *   `app/Environment/Development.php`
    *   `app/Environment/Production.php`

2.  **Migrate `App\Config.php` (Optional but Recommended):**
    You can keep `App\Config.php` but it should now take an array in the constructor instead of `Environment`. However, the recommended way is to move configuration to `config/*.php` files.

    **Example `config/app.php`:**
    ```php
    use Monolog\Logger;
    use App\Environment;

    /** @var Environment $environment */

    return [
        'showErrors' => (bool)env('APP_SHOW_ERRORS', false),
        'cacheConfig' => (bool)env('APP_CACHE_CONFIG', true),
        'logLevel' => Logger::toMonologLevel(env('LOG_LEVEL', Logger::WARNING)),
        'trustedProxies' => (array)env('TRUSTED_PROXIES', []),
        'logPath' => $environment->logPath('riki.log'),
    ];
    ```

    **If you keep `App\Config.php`:**
    Update the constructor to accept an array and use the global `env()` helper.

    ```php
    public function __construct(array $config)
    {
        parent::__construct($config);
        // ...
        $this->dbConfig = new DbConfig(
            'mysql',
            env('DB_DATABASE', 'week_planner'),
            env('DB_USERNAME', env('DB_USER', 'wpuser')),
            env('DB_PASSWORD'),
            env('DB_HOST', 'mysql'),
            env('DB_PORT', '3306')
        );
    }
    ```

3.  **Create `.env.example`:**
    It's good practice to add a `.env.example` file to your project root.

    ```
    # .env.example
    APP_SHOW_ERRORS=true
    APP_CACHE_CONFIG=false
    LOG_LEVEL=debug
    TRUSTED_PROXIES=[]
    ```

### 3. Code Updates

Accessing configuration values has changed. Instead of using the `$app->config` or `$app->environment` properties, use the new global `config()` helper function.

**Before:**

```php
// Old way
if ($this->app->environment->canShowErrors()) {
    // ...
}
$logLevel = $this->container->config->logLevel;
```

**Now:**

```php
// New way, using the config() helper
if (config('app.showErrors', false)) {
    // ...
}
$logLevel = config('app.logLevel', Logger::DEBUG);
```

The first parameter of `config()` is the key of the configuration value, using dot-notation (`<file>.<key>`). The second parameter is an optional default value.

### 4. Config Caching

The config caching command has been updated to work with the new system. It now relies on the `app.cacheConfig` value from your configuration and uses the `Application::rebuildConfigurationCache()` method.

If you have a custom `app/Cli/Command/Config/Cache.php`, you should update it. You can use the one from the quickstart template as a reference. The key changes are:
*   Check `config('app.cacheConfig', true)` to see if caching is allowed.
*   Use `$this->app->rebuildConfigurationCache()` to write the cache file.
*   The cache path is now determined by `$this->app->environment->cachePath('config.dat')`.

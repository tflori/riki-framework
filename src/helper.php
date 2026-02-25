<?php

use Riki\Application;

if (!function_exists('app')) {
    /**
     * @template T of object
     * @param string|class-string<T>|null $class
     * @param array $args
     * @return Application|T|mixed
     */
    function app(string $class = null, ...$args) {
        if (!$class) {
            return Application::app();
        }

        if (class_exists($class)) {
            return Application::app()->make($class, ...$args);
        }

        return Application::app()->get($class, ...$args);
    }
}

if (!function_exists('env')) {
    function env(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return Application::environment();
        }
        return Application::environment()->get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return Application::config();
        }
        return Application::config()->get($key, $default);
    }
}


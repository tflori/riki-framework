<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return \Riki\Application::environment()->get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return \Riki\Application::config();
        }
        return \Riki\Application::config()->get($key, $default);
    }
}

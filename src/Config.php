<?php

namespace Riki;

/**
 * Class Config
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 */
class Config implements \ArrayAccess
{
    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public static function fromFiles(array $files, Environment $environment): static {
        $config = [];
        foreach ($files as $key => $path) {
            $result = (function ($environment, $path) {
                return include $path;
            })($environment, $path);

            if (is_array($result)) {
                $config[$key] = $result;
            }
        }

        return new static($config);
    }

    /**
     * @param $key
     * @param $default
     * @return Config|mixed|null
     */
    public function get($key, $default = null)
    {
        $parts = explode('.', $key);
        $current = $this->config;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $this->toConfig($default);
            }
            $current = $current[$part];
        }

        return $this->toConfig($current);
    }
    
    public function set($key, $value)
    {
        $parts = explode('.', $key);
        $current = &$this->config;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
    }
    
    public function push($key, $value)
    {
        $this->set($key, array_merge($this->get($key, []), [$value]));
    }

    protected function toConfig($value)
    {
        $isAssoc = is_array($value) &&
            !empty($value) &&
            array_keys($value) !== range(0, count($value) - 1);
        return $isAssoc ? new static($value) : $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->toConfig($this->config[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        return $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}

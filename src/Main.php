<?php

namespace Riki;

abstract class Main
{
    /**
     * Call $handler with $args
     *
     * A handler can be a callable or a definition to call method@class. The class in this case have to be instantiable
     * without arguments.
     *
     * @param string|callable $handler
     * @param array           ...$args
     * @throws Exception
     */
    protected function callHandler($handler, ...$args)
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $args);
        } elseif (preg_match('/^([^ ]+)@([^ ]+)$/', $handler, $match) && class_exists($match[2])) {
            call_user_func_array([$this->getController($match[2]), $match[1]], $args);
        } else {
            throw new Exception('Handler not callable');
        }
    }

    /**
     * Returns an instance of $class
     *
     * Typically $class is a controller - you might want to implement a ControllerFactory or DependencyInjection here.
     *
     * @param $class
     * @return mixed
     */
    protected function getController($class)
    {
        return new $class;
    }
}

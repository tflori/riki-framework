<?php

namespace Riki;

/**
 * Class Kernel
 *
 * A Kernel is the main entry point to handle a request, a command or whatever type of "task"
 * you define your application can handle.
 *
 * It requires a method handle. Typically this method requires some sort of "input" and returns
 * "output" - for a CLI kernel that might be `argv` and an integer; for an HTTP kernel a
 * Request and Response object.
 *
 * @package Riki
 * @author  Thomas Flori <thflori@gmail.com>
 * @method mixed handle(...$args)
 */
abstract class Kernel
{
}

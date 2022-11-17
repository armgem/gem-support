<?php

namespace GemSupport\Traits;

use BadMethodCallException;

trait BadAction
{
    /**
     * Throw a bad method call exception for the given method.
     *
     * @param  string  $method
     * @return void
     *
     * @throws \BadMethodCallException
     */
    protected static function throwBadMethodCallException($method)
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }

    /**
     * Throw a bad method call exception for the given method.
     *
     * @param  string  $message
     * @return void
     *
     * @throws \BadMethodCallException
     */
    protected static function throwBadMethodException($message)
    {
        throw new BadMethodCallException($message);
    }
    /**
     * Throw a invalid methods call exception for the given array.
     *
     * @param  string  $methods
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected static function throwInvalidMethodsCallException($methods)
    {
        throw new \InvalidArgumentException(sprintf(
            'None of the methods in the given array [%s] not exist in %s', implode(', ', $methods), static::class
        ));
    }

    /**
     * Throw a invalid methods call exception for the given array.
     *
     * @param  string  $message
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected static function throwInvalidArgumentException($message)
    {
        throw new \InvalidArgumentException($message);
    }

}

<?php

namespace GemSupport\Traits;

trait SelfCalls
{
    use BadAction;

    /**
     * Call first existing methods .
     *
     * @param  array  $methods
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    /**
     * @param $methods
     * @param array $parameters
     * @param null $defaultResult
     * @return mixed
     */
    protected function callFirst($methods, $parameters = [], $defaultResult = null)
    {
        if (! is_array($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                return $this->{$method}(...$parameters);
            }
        }

        if (is_null($defaultResult)) {
            static::throwInvalidMethodsCallException($methods);
        }

        if (is_callable($defaultResult)) {
            return $defaultResult();
        }
        return $defaultResult;
    }
}

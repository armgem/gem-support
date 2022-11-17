<?php

namespace GemSupport\Traits;

use Illuminate\Support\Str;

trait ClassIdentifierTrait
{
    use SelfCalls;

    /**
     * @var bool
     */
    protected $isShortIdentifier = true;

    /**
     * @var array
     */
    protected $identifierMethods  = ['getResource', 'getTable'];


    /**
     * @param null $class
     * @return mixed
     */
    public function getClassIdentifier($class = null)
    {
        $class = $class ?? self::class;
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $this->callFirst($this->identifierMethods, [], function () use ($class) {

            $identifiers = []; // @TODO get in config

            if (! empty($identifiers) && in_array($class, $identifiers)) {
                return array_search($class, $identifiers, true);
            }

            if (! $this->isShortIdentifier) {
                return $class;
            }

            $class = class_basename($class);
            return Str::snake($class);
        });
    }
}

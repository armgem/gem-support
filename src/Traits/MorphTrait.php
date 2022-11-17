<?php

namespace GemSupport\Traits;

trait MorphTrait
{
    use  SelfCalls;

    /**
     * @return mixed|string
     */
    public function getMorphClass()
    {
        return $this->callFirst(['getResource', 'getTable'], [], function () {
            return parent::getMorphClass();
        });
    }
}

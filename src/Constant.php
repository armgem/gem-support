<?php

namespace GemSupport;

class Constant {

    /**
     * @param bool $isFlip
     * @return array|int[]|string[]
     */
    public static function constants(bool $isFlip = true)
    {
        $reflection = new \ReflectionClass(static::class);
        $constants = $reflection->getConstants();

        if ($isFlip) {
            $constants = array_flip($constants);
        }

        return $constants;
    }
}

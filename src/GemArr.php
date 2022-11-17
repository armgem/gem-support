<?php

namespace GemSupport;

use Illuminate\Support\Arr;

class GemArr extends Arr
{
    /**
     * implode array with wrap
     *
     * @param array $data
     * @param string $before
     * @param string|null $after
     * @return string
     */
    public static function implodeWrap(array $data, string $before, ? string $after = null): string
    {
        $after = $after ?? $before;
        return $before . implode($after . $before, $data) . $before;
    }

    /**
     * returns array with options for select box
     *
     * @param mixed $min
     * @param mixed $max
     * @param int $step
     * @return array
     */
    public static function combineRange(mixed $min, mixed $max, int $step = 1): array
    {
        return array_combine(range($min, $max, $step), range($min, $max, $step));
    }


    /**
     * returns the first key of the array
     *
     * @param array $array
     * @return null|int|string
     */
    public static function firstKey(array $array): null|int|string
    {
        reset($array);
        return key($array);
    }

    /**
     * returns the last key of the array
     *
     * @param array $array
     * @return null|int|string
     */
    public static function lastKey(array $array): null|int|string
    {
        $array = array_reverse($array, true);
        reset($array);
        return key($array);
    }

    /**
     * case-insensitive array_unique
     *
     * @param array
     * @return array
     * @link http://stackoverflow.com/a/2276400/932473
     */
    public static function iUnique(array $array): array
    {
        $lowered = array_map('mb_strtolower', $array);
        return array_intersect_key($array, array_unique($lowered));
    }

    /**
     * case-insensitive in_array
     *
     * @link http://us2.php.net/manual/en/function.in-array.php#89256
     * @link https://stackoverflow.com/a/2166524
     * @link https://stackoverflow.com/a/2166522
     *
     * @param string $needle
     * @param array $haystack
     * @return bool
     */
    public static function inArrayI(string $needle, array $haystack): bool
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }

    /**
     * check if array's keys are all numeric
     *
     * @param array
     * @return bool
     * @link https://codereview.stackexchange.com/q/201/32948
     */
    public static function isNumeric(array $array): int
    {
        foreach ($array as $k => $v) {
            if (!is_int($k)) {
                return false;
            }
        }

        return true;
    }

}

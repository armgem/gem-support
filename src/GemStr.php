<?php

namespace GemSupport;

use Illuminate\Support\Str;

class GemStr extends Str
{
    const LAST_OCCURRENCE = 0;

    /**
     * return all positions give substring
     *
     * @param string $string
     * @param string $search
     * @param bool $caseSensitive
     * @return array
     */
    public static function positions(string $string, string $search, bool $caseSensitive = false): array
    {
        $lastPos = 0;
        $positions = [];
        $length = strlen($search);
        $number = 1;
        $method = $caseSensitive ? 'stripos' : 'strpos';

        while (($lastPos = $method($string, $search, $lastPos)) !== false) {
            $positions[$number++] = $lastPos;
            $lastPos = $lastPos + $length;
        }

        return $positions;
    }

    /**
     * return occurrence positions give substring
     *
     * @param string $string
     * @param string $search
     * @param int $position
     * @param bool $caseSensitive
     * @return array
     */
    public static function occurrencePosition(string $string, string $search, int $occurrence, bool $caseSensitive = false): int|bool
    {
        if ($occurrence == self::LAST_OCCURRENCE) {
            $method = $caseSensitive ? 'strripos' : 'strrpos';
            return $method($string, $search);
        }


        $lastPos = 0;
        $length = strlen($search);
        $currentOccurrence = 1;
        $method = $caseSensitive ? 'stripos' : 'strpos';

        while (($lastPos = $method($string, $search, $lastPos)) !== false) {
            if ($currentOccurrence == $occurrence) {
                return $lastPos;
            }
            $lastPos = $lastPos + $length;
            $currentOccurrence++;
        }

        return false;
    }

    /**
     * Return the remainder of a string after occurrence of a given value.
     *
     * @param string $subject
     * @param string $search
     * @param int $occurrence
     * @param bool $caseSensitive
     * @return string|false
     */
    public static function afterOccurrence(string $subject, string $search, int $occurrence = 1, bool $caseSensitive = false): string|false
    {
        if ($search == '') {
            return $subject;
        }

        $position = static::occurrencePosition($subject, $search, $occurrence, $caseSensitive);

        return false === $position ? false : substr($subject, $position + strlen($search));
    }

    /**
     * Get the portion of a string before occurrence of a given value.
     *
     * @param string $subject
     * @param string $search
     * @param int $occurrence
     * @param bool $caseSensitive
     * @return string|false
     */
    public static function beforeOccurrence(string $subject, string $search, int $occurrence = self::LAST_OCCURRENCE, bool $caseSensitive = true): string|false
    {
        if ($search == '') {
            return $subject;
        }

        $position = static::occurrencePosition($subject, $search, $occurrence, $caseSensitive);

        return false === $position ? false : substr($subject, 0, $position);
    }

    /**
     * Get the portion of a string between two given values based start and end occurrence.
     *
     * @param string $subject
     * @param string $startSearch
     * @param string $endSearch
     * @param int $startOccurrence
     * @param int $endOccurrence
     * @param bool $caseSensitive
     * @return string|bool
     */
    public static function betweenOccurrences(string $subject, string $startSearch, string $endSearch, int $startOccurrence = 1, int $endOccurrence = self::LAST_OCCURRENCE, bool $caseSensitive = false): string|bool
    {
        if ($startSearch == '' || $endSearch == '') {
            return $subject;
        }

        $startPosition = static::occurrencePosition($subject, $startSearch, $startOccurrence, $caseSensitive);
        $endPosition = static::occurrencePosition($subject, $endSearch, $endOccurrence, $caseSensitive);

        if (false === $startPosition || false === $endPosition) {
            return false;
        }

        $startPosition += strlen($startSearch);

        return $startPosition > $endPosition ? false : substr($subject, $startPosition, $endPosition - $startPosition);
    }

    /**
     * Wrap the substring from string with the given strings based positions.
     *
     * @param string $string
     * @param string $search
     * @param string $before
     * @param string|null $after
     * @param array $occurrences
     * @param bool $caseSensitive
     * @return string
     */
    public static function wrapSubBasedOccurrences(string $string, string $search, string $before, ?string $after = null, array $occurrences = [], bool $caseSensitive = false): string
    {
        $positions = self::positions($string, $search, $caseSensitive);

        if (empty($positions)) {
            return $string;
        }
        $occurrences = $occurrences ?: array_keys($positions);

        rsort($occurrences);

        $length = strlen($search);
        $wrapped = $before . $search . ($after ??= $before);

        foreach ($occurrences as $occurence) {
            $position = $positions[$occurence] ?? false;
            if (false === $position) {
                continue;
            }

            $string = substr_replace($string, $wrapped, $position, $length);
        }

        return $string;
    }

    /**
     * Convenience method for htmlspecialchars.
     *
     * @param string|array|object|bool $text Text to wrap through htmlspecialchars. Also works with arrays, and objects.
     *    Arrays will be mapped and have all their elements escaped. Objects will be string cast if they
     *    implement a `__toString` method. Otherwise the class name will be used.
     * @param string|bool $double Encode existing html entities.
     * @param string|null $charset Character set to use when escaping. Defaults to config value in `mb_internal_encoding()`
     * or 'UTF-8'.
     * @return array|bool|string Wrapped text.
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#h
     */
    public static function htmlspecialchars(string|array|object|bool $text, string|bool $double = true, ?string $charset = null)
    {
        if (is_string($text)) {
            //optimize for strings
        } elseif (is_array($text)) {
            $texts = [];
            foreach ($text as $k => $t) {
                $texts[$k] = self::htmlspecialchars($t, $double, $charset);
            }
            return $texts;
        } elseif (is_object($text)) {
            if (method_exists($text, '__toString')) {
                $text = (string)$text;
            } else {
                $text = '(object)' . get_class($text);
            }
        } elseif (is_bool($text)) {
            return $text;
        }

        static $defaultCharset = false;

        if ($defaultCharset === false) {
            $defaultCharset = mb_internal_encoding();
            if ($defaultCharset === null) {
                $defaultCharset = 'UTF-8';
            }
        }

        if (is_string($double)) {
            $charset = $double;
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, $charset ?: $defaultCharset, $double);
    }

    /**
     * returns given amount of characters counting backwards
     *
     * @param string $str
     * @param int $count
     * @return string
     */
    public static function lastChars(string $str, int $count = 1): string
    {
        return mb_substr($str, -$count, $count);
    }

    /**
     * Humanize give string
     *
     * @param string $val
     * @return string
     */
    public static function humanize(string $val): string
    {
        // TODO make function
        return self::headline($val);
    }

    /**
     * returns the short string based on $length if string's length is more than $length
     *
     * @param string $str
     * @param int $length
     * @param bool $raw
     * @return string
     */
    public static function shortenSpan(string $str, int $length = 50, bool $raw = false): string
    {
        if (mb_strlen($str) <= $length) {
            return self::htmlspecialchars($str);
        }

        $shortStr = mb_substr($str, 0, $length) . "...";

        if ($raw) {
            return self::htmlspecialchars($shortStr);
        }

        return '<span title="' . self::htmlspecialchars(str_ireplace("/", "", $str)) . '">' . self::htmlspecialchars($shortStr) . '</span>';
    }

    /**
     * @param int $occurrence
     * @param array $positions
     * @return int
     */
    protected static function processOccurrence(int $occurrence, array $positions): int
    {
        return $occurrence == self::LAST_OCCURRENCE ? GemArr::lastKey($positions) : $occurrence;
    }
}

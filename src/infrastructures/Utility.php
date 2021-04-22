<?php

namespace Aweklin\Paystack\Infrastructures;

/**
 * Contains utility & helper methods. 
 * The methods here are extracts from KlinPHP, a clean, robust, secured and slim PHP MVC framework.
 */
final class Utility {

    
    /**
     * Checks if the value passed is null or empty and returns a value indicating the status of the check.
     * 
     * @param mixed $value The value being checked.
     */
    public static function isEmpty($value) : bool {
        if (!$value) return true;
        if (\is_string($value) && mb_strlen(trim($value)) == 0) return true;
        return empty($value);
    }

    /**
     * Checks if the given string contains the search phrase.
     * 
     * @param string $string The full string to search through.
     * @param string $search The text being searched.
     * 
     * @return bool
     */
    public static function contains(string $string, string $search) : bool {
        if ($string) $string = trim($string);
        return mb_strpos($string, $search) !== false;
    }

    /**
     * Checks if the passed in array value is an associative array.
     * 
     * @param array $arr The array being checked.
     * 
     * @return bool
     */
    public static function isAssociative(array $arr): bool {
        // credit: https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Converts a given object to its array equivalent.
     * 
     * @param object $object The object to be converted. Null can be passed, but this means it will return an empty array.
     * 
     * @return array
     */
    public static function convertObjectToArray($object = null) : array {
        if (!$object) return [];

        return \json_decode(\json_encode($object), true);
    }

    /**
     * Checks if the search string is the first occurrence in the subject.
     * 
     * @param string $string The text being searched.
     * @param string $search The actual text being searched.
     * @param bool $ignoreCase Case sensitivity is ignored by default. When false or null is specified, the result matches the case for comparison.
     * 
     * @return bool
     */
    public static function startsWith(string $string, string $search, bool $ignoreCase = true) : bool {
        if (self::isEmpty($string)) return false;
        if (\mb_strlen($search) == 0 && $search !== \mb_substr($string, 0, 1)) return false;
        return substr_compare($string, $search, 0, mb_strlen($search), $ignoreCase) === 0;
    }

    /**
     * Checks if the search string is the last occurrence in the subject.
     * 
     * @param string $string The text being searched.
     * @param string $search The actual text being searched.
     * @param bool $ignoreCase Case sensitivity is ignored by default. When false or null is specified, the result matches the case for comparison.
     * 
     * @return bool
     */
    public static function endsWith(string $string, string $search, bool $ignoreCase = true) : bool {
        if (self::isEmpty($string)) return false;
        if (\mb_strlen($search) == 0 && $search !== \mb_substr($string, \mb_strlen($string) - 1, 1)) return false;
        return substr_compare($string, $search, -mb_strlen($search), null, $ignoreCase) === 0;
    }

    /**
     * Checks if the given string contains a numeric value.
     * 
     * @param string $string The value being checked.
     * 
     * @return bool
     */
    public static function containsNumber(string $string) : bool {
        // credit: https://board.phpbuilder.com/d/10366614-resolved-determine-if-a-string-contains-numbers-or-not
        return \preg_match('#[0-9]#', $string);
    }

    /**
     * Checks if the given value has only alphabet, irrespective of its case.
     * 
     * @param string $string The value being checked.
     * 
     * @return bool
     */
    public static function isAlphabetOnly(string $string) : bool {
        return \ctype_alpha($string);
    }

    /**
     * Determines if there is Internet access.
     * 
     * @return bool
     */
    public static function hasInternetAccess() : bool {
        $isConnected = false;

        // check to see if network state is normal
        if (\connection_status() == \CONNECTION_NORMAL) {
            
            // verify by establishing connection to google.com
            $connectionHandler = @\fopen('http://www.google.com:80/', 'r');
            if ($connectionHandler) {
                $isConnected = true;
                \fclose($connectionHandler);
            }
        }

        return $isConnected;
    }

    /**
     * Checks if the specified value is valid datetime.
     * 
     * @param string $date A date/time value.
     * 
     * @return boolean
     */
    public static function isValidDate(string $date, string $timeZone = 'GMT+1'): bool {
        try {
            if (self::isEmpty($date)) return false;
            new \DateTime($date, new \DateTimeZone($timeZone));   //TODO:: refine this to ensure an actual invalid date is recognized as invalid because it failed for 202020-09-23
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Attempts to parse the given value as string and throws an Exception if not convertible to string. The result is left and right trimmed.
     * 
     * @param mixed $value The value being parsed as string.
     * 
     * @throws \Exception
     * 
     * @return string
     */
    public static function parseString($value) : string {
        if (\is_array($value) || \is_object($value) || \is_file($value) || \is_dir($value))
            throw new \Exception(\gettype($value) . ' cannot be casted to a string.');

        return trim(((string) $value));
    }

}
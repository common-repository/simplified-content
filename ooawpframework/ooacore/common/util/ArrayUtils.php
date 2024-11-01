<?php

/**
 * Array utilities
 *
 * @author mark
 *
 */
class ArrayUtils
{


    /**
     * Return a boolean if the array is associative
     *
     * @param $array
     */
    public static function isAssociative($array)
    {
        return array_values($array) !== $array;
    }

    /**
     * Take an associative array as input and return a new array with all the original keys prefixed by the
     * passed prefix.
     *
     * @param array $array
     * @param string $prefix
     *
     * @return array
     */
    public static function prefixArrayKeys($array, $prefix)
    {

        $prefixedArray = array();
        foreach ($array as $key => $value) {
            $prefixedArray [$prefix . $key] = $value;
        }

        return $prefixedArray;

    }

    /**
     * Find all parameters in the passed array with a key starting with the supplied prefix.
     * Return them as an associative array by key.
     *
     * @param array $array
     * @param string $prefix
     *
     * @return array
     */
    public static function getAllArrayItemsByKeyPrefix($array, $prefix)
    {
        $returnValues = array();
        foreach ($array as $key => $value) {
            $positionOfPrefix = strpos($key, $prefix);
            if (is_numeric($positionOfPrefix) && $positionOfPrefix == 0) {
                $returnValues [$key] = $value;
            }
        }

        return $returnValues;
    }


    /**
     * Handy alternative to direct array access, particularly useful when
     * accessing array elements from function return values.
     *
     * @param $array
     * @param $key
     */
    public static function arrayElementValue($array, $key)
    {
        return $array[$key];
    }


    /**
     * Diff function for arrays which works with arrays of objects as well as string comparables.
     *
     * @param $array1
     * @param $array2
     */
    public static function arrayDiff($array1, $array2)
    {
        return array_udiff($array1, $array2,
            function ($a, $b) {
                if (is_object($a)) {
                    return strcmp(spl_object_hash($a), spl_object_hash($b));
                } else if (is_array($a)) {
                    return ArrayUtils::arrayDiff($a, $b);
                } else {
                    return strcmp($a, $b);
                }
            }
        );
    }

}

?>
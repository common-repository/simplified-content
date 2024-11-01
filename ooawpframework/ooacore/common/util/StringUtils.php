<?php

/**
 * Static string utilities
 *
 * @author mark
 *
 */
class StringUtils {

    /**
     * Return a boolean indicating whether the passed string matches the supplied pattern
     * which may contain * wildcards
     *
     * @param string $predicateString
     * @param string $pattern
     */
    public static function matchesWildcardPattern($predicateString, $pattern) {

        // Only do a fuzzy match if at least a single * character found.
        if (is_numeric(strpos($pattern, "*"))) {
            $pattern = str_replace("\\*", "(.*)", preg_quote($pattern, "/"));
            $regex = "/" . $pattern . "/";
            return preg_match($regex, $predicateString) > 0;

        } else {
            return $predicateString == $pattern;
        }

    }


    /**
     * Generate a random string of the given length (for e.g. passwords etc).
     * Optional flags allow for capital letters, numbers and symbols
     *
     * @param $numberOfChars
     * @param bool $includeSymbols
     */
    public static function generateRandomString($numberOfChars, $includeCaps = true, $includeNumbers = true, $includeSymbols = false) {

        $possibleChars = range(97, 122);

        // If include caps, add in capital letters
        if ($includeCaps) $possibleChars = array_merge($possibleChars, range(65, 90));
        if ($includeNumbers) $possibleChars = array_merge($possibleChars, range(48, 57));
        if ($includeSymbols) $possibleChars = array_merge($possibleChars, array(33, 64, 35, 43, 61, 42, 63));


        $string = "";
        for ($i = 0; $i < $numberOfChars; $i++) {
            $string .= chr($possibleChars[rand(0, sizeof($possibleChars) - 1)]);
        }


        return $string;
    }

}

?>
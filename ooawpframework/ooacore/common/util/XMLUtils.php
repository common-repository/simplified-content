<?php

/**
 * Static XML Utils for miscellaneous actions on XML
 */
class XMLUtils {

    /**
     * Pretty print an XML string - if the HTML Display flag is passed it will be formatted for display on a web page
     * with special characters escaped.
     *
     * @param string $xmlString
     * @param boolean $htmlDisplay
     *
     * @return string
     */
    public static function prettyPrint($xmlString, $htmlDisplay = false) {

        // Create a nicely formatted version
        $doc = new DOMDocument();
        $doc->loadXML($xmlString);
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $prettyXML = $doc->saveXML();

        if ($htmlDisplay) $prettyXML = str_replace(array("<", ">", "\n", " "), array("&lt;", "&gt;", "<br>", "&nbsp;"), $prettyXML);

        return $prettyXML;
    }

}
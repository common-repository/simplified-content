<?php


/**
 * Form field utilities
 * Static class functions
 */


class FieldUtils 
{
	

	/**
     * Utility function to check for valid hexadecimal colour input
     *
     * @param string  $colour Colour value from input
     * @return string         Cleaned colour value with hash added
     */
 	public static function checkHexColour( $colour ) {

 		$fixColour = null;

        //Check for a hex colour string '#c1c2b4'
        if ( preg_match( '/^#[a-f0-9]{6}$/i', $colour ) ) //hex colour is valid
            {
            $fixColour = $colour;
        }

        //Check for a hex colour string without hash 'c1c2b4'
        else if ( preg_match( '/^[a-f0-9]{6}$/i', $colour ) ) //hex colour is valid
                {
                $fixColour = '#' . $colour;
            }

        return $fixColour;
    }

    /**
     * Add http:// to beginning of url if missing
     *
     * @param string  $url URL string
     * @return string      URL in the format http://www.url.com
     */
    public static function addHttp( $url ) {
        if ( !preg_match( "~^(?:f|ht)tps?://~i", $url ) ) {
            $url = "http://" . $url;
        }
        return $url;
    }
}



?>
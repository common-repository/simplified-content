<?php

/**
 * Normalise property names (i.e. produce camel case representation)
 * Normally, this involves simply lower casing the first character, but sometimes if there are multiple 
 * capital letters involved it gets a little bit more complicated.
 * 
 * @author mark
 *
 */
class PropertyNameNormaliser {
	
	private static $instance;
	
	// Only allow singleton behaviour
	private function PropertyNameNormaliser() {
	}
	
	/**
	 * Return the singleton instance.
	 * 
	 * @return PropertyNameNormaliser
	 */
	public static function instance() {
		if (! PropertyNameNormaliser::$instance) {
			PropertyNameNormaliser::$instance = new PropertyNameNormaliser ();
		}
		
		return PropertyNameNormaliser::$instance;
	}
	
	/**
	 * Normalise the property name using the standard rules we might expect.
	 * 
	 * @param string $propertyName
	 */
	public function normalisePropertyName($propertyName) {
		
		if ((strlen ( $propertyName ) > 1) && ((ord ( $propertyName [1] ) >= ord ( "a" )) || is_numeric ( $propertyName [1] ))) {
			$propertyName = strtolower ( $propertyName [0] ) . substr ( $propertyName, 1 );
		}
		
		return $propertyName;
	}

}

?>
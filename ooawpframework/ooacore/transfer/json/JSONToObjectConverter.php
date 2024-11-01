<?php

include_once OOA_CORE_ROOT . "/common/util/ClassUtils.php";
include_once OOA_CORE_ROOT . "/transfer/framework/FormatToObjectConverter.php";

class JSONToObjectConverter implements FormatToObjectConverter {
	
	/**
	 * Convert a json string into objects
	 *
	 * @param string $phpSerialString
	 */
	public function convert($phpSerialString) {
		
		// Decode the string using PHP JSON Decode routine
		$converted = json_decode ( $phpSerialString, true );
		
		// Now convert to objects and return
		return $this->expandObjects ( $converted );
	}
	
	/**
	 * Expand a JSON Item into full OOA Objects if required.  This is called recursively 
	 * as required to expand the whole object hierarchy.
	 *
	 * @param mixed $jsonArray
	 */
	private function expandObjects($jsonItem) {
		
		// If an array, process this array to see whether or not it needs to be recast into an object.
		if (is_array ( $jsonItem )) {
			
			// Ensure each sub item is correctly expanded recursively
			foreach ( $jsonItem as $key => $value ) {
				$jsonItem [$key] = $this->expandObjects ( $value );
			}
			
			// Now deal with an object if required
			if (array_key_exists ( "className", $jsonItem )) {
				$className = $jsonItem ["className"];
                unset($jsonItem["className"]);
			} else {
				$className = null;
			}
			
			if ($className) {
				
				// Create a new instance to work with and an inspector to handle mappings
				$newObject = ClassUtils::createNewClassInstance ( $className, null, true );
				
				// Map each associative entry to a 
				foreach ( $jsonItem as $key => $value ) {
					
					try {
						$newObject->__setSerialisablePropertyValue ( $key, $value );
					} catch ( PropertyNotWritableException $e ) {
						// Ignore property not writable exceptions.
					}
				}
				
				// Make the current json Item the new object ready for return
				$jsonItem = $newObject;
			
			}
		
		}
		
		return $jsonItem;
	
	}

}

?>
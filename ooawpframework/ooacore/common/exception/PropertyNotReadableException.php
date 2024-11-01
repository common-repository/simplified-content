<?php

/**
 * Exception raised if an attempt is made to read a property which does not exist.
 * 
 * @author mark
 *
 */
class PropertyNotReadableException extends Exception {
	
	public function PropertyNotReadableException($className, $propertyName) {
		parent::__construct ( "An attempt was made to read the property '" . $propertyName . "' on the class '" . $className . "' Which has no public / protected member or get method." );
	}

}

?>
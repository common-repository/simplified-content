<?php

/**
 * Exception raised if an attempt is made to call a none existent method for a dynamic serialisable object
 * 
 * @author mark
 *
 */
class NoneExistentMethodException extends Exception {
	
	/**
	 * Construct with class name and method name.
	 * 
	 * @param string $className
	 * @param string $methodName
	 */
	public function NoneExistentMethodException($className, $methodName) {
		parent::__construct ( "An attempt was made to access the none existent method '" . $methodName . "' on the class '" . $className . "'" );
	}

}

?>
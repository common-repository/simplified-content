<?php

/*
 * A generic exception which may be thrown for a method which exists but has not yet been properly implemented.
 * 
 */
class MethodNotImplementedException extends Exception {
	
	/**
	 * Construct with the class and method representing the failed method.
	 * 
	 * @param string $className
	 * @param string $methodName
	 */
	public function MethodNotImplementedException($className, $methodName) {
		parent::__construct ( "The method '" . $methodName . "' on the class '" . $className . "' has not yet been implemented" );
	}
}

?>
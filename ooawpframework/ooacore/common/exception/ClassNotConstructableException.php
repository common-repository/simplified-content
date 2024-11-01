<?php

/**
 * Class not constructable exception.  Raised if an attempt is made to create a class using the blank constructor 
 * when no blank constructor exists.
 * 
 * @author mark
 *
 */
class ClassNotConstructableException extends Exception {
	
	public function ClassNotConstructableException($className = null) {
		parent::__construct ( "An attempt was made to construct the class '" . $className . "' using a blank constructor for which none exists" );
	}

}

?>
<?php

/**
 * Generic class not found exception
 * 
 * @author mark
 *
 */
class ClassNotFoundException extends Exception {
	
	public function ClassNotFoundException($className = null) {
		parent::__construct ( "The class '" . $className . "' supplied does not exist or has not yet been loaded" );
	}

}

?>
<?php

class ClassNotSerialisableException extends Exception {
	
	/**
	 * Exception raised if an attempt is made to utilise serialisable behaviour on an object which is not serialisable.
	 * 
	 * @param string $object
	 */
	public function ClassNotSerialisableException($className = null) {
		parent::__construct ( "An attempt was made to access serialisable properties for an object of type '" . $className . "' which is not serialisable" );
	}

}

?>
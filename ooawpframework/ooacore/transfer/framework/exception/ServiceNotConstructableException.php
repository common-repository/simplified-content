<?php

/**
 * Exception raised if an attempt is made to construct a service which has not got a blank constructor.
 * 
 * @author mark
 *
 */
class ServiceNotConstructableException extends Exception {
	
	/**
	 * Raise the exception with the service name
	 * 
	 * @param string $serviceName
	 */
	public function ServiceNotConstructableException($serviceName) {
		parent::__construct ( "An attempt was made to access the service '" . $serviceName . "' which is not constructable as it has no blank constructor" );
	}

}

?>
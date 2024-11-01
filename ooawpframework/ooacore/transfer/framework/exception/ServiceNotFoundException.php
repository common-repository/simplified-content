<?php

/**
 * Exception raised if an attempt is made to access a service which cannot be found via the dispatcher.
 * 
 * @author mark
 *
 */
class ServiceNotFoundException extends Exception {
	
	public function ServiceNotFoundException($serviceName) {
		parent::__construct ( "An attempt was made to access the service '" . $serviceName . "' which does not exist" );
	}

}

?>
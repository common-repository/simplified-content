<?php

/**
 * Exception raised if an attempt is made to access a service method which cannot be found within the supplied service via the dispatcher.
 * 
 * @author mark
 *
 */
class ServiceMethodNotFoundException extends Exception {
	
	public function ServiceMethodNotFoundException($serviceName, $serviceMethod) {
		parent::__construct ( "An attempt was made to access the method '" . $serviceMethod . "' on the service '" . $serviceName . "'.  Whilst the service does exist, the method does not exist" );
	}

}

?>
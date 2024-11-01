<?php

/**
 * Exception raised if a request is made through the service dispatcher with no service method url fragment.
 * 
 * @author mark
 *
 */
class NoServiceMethodSuppliedException extends Exception {
	
	/**
	 * Construct with the service url supplied
	 * 
	 * @param string $url
	 */
	public function NoServiceMethodSuppliedException($url) {
		parent::__construct ( "The url supplied '" . $url . "' contains a service name fragment, but does not contain a valid service method name fragment" );
	}

}

?>
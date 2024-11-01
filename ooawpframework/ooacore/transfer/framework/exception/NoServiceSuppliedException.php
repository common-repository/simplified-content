<?php

/**
 * Exception raised if a request is made through the service dispatcher with no service url fragment.
 * 
 * @author mark
 *
 */
class NoServiceSuppliedException extends Exception {
	
	/**
	 * Construct with the service url supplied
	 * 
	 * @param string $url
	 */
	public function NoServiceSuppliedException($url) {
		parent::__construct ( "The url supplied '" . $url . "' does not contain a valid service name fragment" );
	}

}

?>
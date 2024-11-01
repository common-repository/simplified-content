<?php

/**
 * Exception raised if too few method parameters are passed to a service method. 
 * 
 * @author mark
 *
 */
class TooFewServiceMethodParametersException extends Exception {
	
	/**
	 * Construct with required data to write an informed message
	 * 
	 * @param string $serviceName
	 * @param string $serviceMethod
	 * @param integer $parametersPassed
	 * @param integer $parametersRequired
	 */
	public function TooFewServiceMethodParametersException($serviceName, $serviceMethod, $parametersPassed, $parametersRequired) {
		parent::__construct ( "Too few parameters were passed to the service method '" . $serviceMethod . "' on the service '" . $serviceName . "'.  " . $parametersPassed . " were passed, " . $parametersRequired . " are required." );
	}

}

?>
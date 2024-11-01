<?php

/**
 * Exception raised if an exception occurs during execution of a service method.  This is constructed with a wrapped exception return value
 * which should be extracted.
 * 
 * @author mark
 *
 */
class ServiceRuntimeException extends Exception {
	
	private $serviceException;
	
	/**
	 * Construct with the return value from the runtime exception
	 * 
	 * @param $wrappedExceptionReturnValue
	 */
	public function ServiceRuntimeException($serviceException) {
		$this->serviceException = $serviceException;
		parent::__construct ( "An exception was raised during a service invocation.  Please check nested exception object for more info" );
	}
	
	/**
	 * @return the $runtimeExceptionReturnValue
	 */
	public function getServiceExceptionObject() {
		return $this->serviceException;
	}

}

?>
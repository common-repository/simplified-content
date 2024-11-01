<?php

/**
 * Generic validation exception which accepts an array of problems keyed in by a field key.
 * 
 * @author oxil
 *
 */
class ValidationException extends Exception {
	
	private $exceptionArray;
	
	/**
	 * Construct with an array of exceptions (usually returned from a validate method) 
	 * and a seperator which defaults to html new line.
	 * 
	 * @param array $exceptionArray
	 */
	public function ValidationException($exceptionArray, $messageSeparator = "<br />") {
		$this->exceptionArray = $exceptionArray;
		parent::__construct ( "The following validation errors occurred:" . $messageSeparator . $messageSeparator . join ( $messageSeparator, $exceptionArray ) );
	}
	
	/**
	 * @return the $exceptionArray
	 */
	public function getExceptionArray() {
		return $this->exceptionArray;
	}

}

?>
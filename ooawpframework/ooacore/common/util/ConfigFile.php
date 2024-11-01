<?php

/**
 * A generic configuration file handling class.  This handles the reading of parameters
 * defined within a standard properties type file format.
 * The constructor takes the path to the file to be read.
 *
 */
class ConfigFile {
	
	private $configFilePath;
	private $parameters = array ();
	
	/**
	 * Construct a config file object using the passed file name.
	 *
	 * @param string $configFilePath
	 * @return Configuration
	 */
	public function ConfigFile($configFilePath = null) {
		
		if (file_exists ( $configFilePath )) {
			$this->parseFile ( $configFilePath );
		}
		
		$this->configFilePath = $configFilePath;
	}
	
	/**
	 * Get the parameter matching the passed key 
	 * or null if non existent
	 *
	 * @param string $key
	 */
	public function getParameter($key) {
		if (array_key_exists ( $key, $this->parameters ))
			return $this->parameters [$key];
		else
			return null;
	}
	
	/**
	 * Add a parameter by key and value
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addParameter($key, $value) {
		$this->parameters [$key] = $value;
	}
	
	/**
	 * Remove a parameter by key
	 *
	 * @param string $key
	 */
	public function removeParameter($key) {
		unset ( $this->parameters [$key] );
	}
	
	/**
	 * Reset the parameters array with a new full array
	 *
	 * @param array $parameters
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}
	
	/**
	 * Get all the parameters
	 *
	 * @param unknown_type $parameters
	 */
	public function getAllParameters() {
		return $this->parameters;
	}
	
	/**
	 * Return the actual text which would be written out by the save method.
	 * This is particularly useful in situations like the PropertiesResource
	 * where the output needs to be injected into a resource.
	 *
	 * @return string
	 */
	public function getConfigFileText() {
		$configFileText = "";
		foreach ( $this->parameters as $key => $value ) {
			$configFileText .= $key . "=" . $value . "\n";
		}
		return $configFileText;
	}
	
	/**
	 * Save the config file back out.  If null supplied for filepath, the constructed configFilePath is used
	 *
	 */
	public function save($filePath = null) {
		
		// Determine the correct file path.
		$filePath = ($filePath == null) ? $this->configFilePath : $filePath;
		
		// Store the file
		file_put_contents ( $filePath, $this->getConfigFileText () );
	}
	
	/**
	 * Parse function.  Splits the config file into lines and
	 * then looks for key value pairs of the form key=value
	 */
	private function parseFile($configFilePath) {
		$configFileText = file_get_contents ( $configFilePath );
		
		// Now split each line on carriage return
		$lines = explode ( "\n", $configFileText );
		
		// Now loop through each line, attempting a split by equals sign to get key value pairs out.
		foreach ( $lines as $line ) {
			
			// Firstly split lines on # to ensure that comments are ignored
			$splitComment = explode ( "#", $line );
			
			$propertyLine = trim ( $splitComment [0] );
			
			//  if the first entry is zero length when trimmed we know the line is a comment so we ignore the whole line
			// Otherwise continue and use the bit before any potential comment
			if (strlen ( $propertyLine ) > 0) {
				
				// Now split into key, value on =
				$positionOfFirstEquals = strpos ( $propertyLine, "=" );
				
				// If there are not 2 or more array entries at this point we should complain unless we meet a blank line.
				if ($positionOfFirstEquals) {
					$this->parameters [trim ( substr ( $propertyLine, 0, $positionOfFirstEquals ) )] = trim ( substr ( $propertyLine, $positionOfFirstEquals + 1 ) );
				} else {
					throw new Exception ( "Error in config file: Parameter '" . $propertyLine . "' Does not have a value" );
				}
			
			}
		}
	}

}

?>
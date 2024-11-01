<?php

include_once OOA_CORE_ROOT . "/common/util/ConfigFile.php";

/**
 * Main Application configuration file.  This is a singleton instance, and has various application specific
 * bits which are useful such as module enumeration.
 *
 */
class Configuration extends ConfigFile {
	
	private static $instance = null;
	private $activeModules = null;
	
	// private only constructor here, since we should only access this through other instance methods.
	private function Configuration() {
		parent::ConfigFile ( "config/config.txt" );
	}
	
	/**
	 * Get the singleton configuration instance.
	 *
	 * @return Configuration
	 */
	public static function instance() {
		if (Configuration::$instance == null) {
			Configuration::$instance = new Configuration ();
		}
		
		return Configuration::$instance;
	}
	
	/**
	 * Static get parameter function.  Convenience quicky for calling internal getParameter
	 *
	 * @param unknown_type $key
	 */
	public static function readParameter($key) {
		return Configuration::instance ()->getParameter ( $key );
	}

}

?>
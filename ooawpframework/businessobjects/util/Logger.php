<?php

class Logger {
	
	/**
	 * Log a message to the wordpress log stream.
	 *
	 */
	public static function log($log, $filename = "", $nameofclass="", $lineNumber = ""){
		// if ( true === WP_DEBUG ) {

            if ( is_array( $log ) || is_object( $log ) ) {

                error_log( print_r( $log, true ) );

            } else {

                error_log( '['. basename($filename) .':'.$nameofclass.':'.$lineNumber.'] '. $log );

            }

        // }
	}


}


?>
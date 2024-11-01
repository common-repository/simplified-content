<?php

/**
 * Generic OperationInterceptor interface.  Interceptors trap calls to operations within the system and provide a mechanism to veto a particular operation 
 * beforehand if required (e.g. for authentication).
 * 
 * The interface is currently very simple with a single method which should return true if the operation is allowed to proceed or false if
 * we wish to veto the operation.
 * 
 * @author mark
 *
 */
interface OperationInterceptor {
	
	/**
	 * This method is called.  If this returns false, the operation is vetoed generically.  Or clearly a more
	 * specific exception can be thrown here if required.
	 * 
	 * @return boolean
	 */
	public function beforeOperation();

}

?>
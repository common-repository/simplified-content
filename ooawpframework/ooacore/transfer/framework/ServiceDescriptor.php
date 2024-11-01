<?php

include_once OOA_CORE_ROOT . "/common/businessobjects/SerialisableObject.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ServiceMethodDescriptor.php";

/**
 * Descriptor object which describes the capabilities of a service, namely it's
 * methods and parameter types.
 *
 * @author mark
 *        
 */
class ServiceDescriptor extends SerialisableObject {
	
	protected $serviceName;
	protected $serviceMethods;
	
	/**
	 * Construct a service descriptor from a passed class name.
	 *
	 * @param $serviceName string       	
	 */
	public function ServiceDescriptor($serviceName = null) {
		$this->serviceName = $serviceName;
		
		if (class_exists ( $serviceName )) {
			$reflectionClass = new ReflectionClass ( $serviceName );
			
			$serviceMethods = array ();
			foreach ( $reflectionClass->getMethods () as $method ) {
				if ($method->getName () != $serviceName && $method->isPublic ())
					$serviceMethods [] = new ServiceMethodDescriptor ( $method );
			}
			$this->serviceMethods = $serviceMethods;
		}
	
	}
	
	/**
	 *
	 * @return the $serviceName
	 */
	public function getServiceName() {
		return $this->serviceName;
	}
	
	/**
	 *
	 * @return the $serviceMethods
	 */
	public function getServiceMethods() {
		return $this->serviceMethods;
	}

}

?>
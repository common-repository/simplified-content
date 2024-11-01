<?php

include_once OOA_CORE_ROOT . "/common/businessobjects/SerialisableObject.php";

/**
 * Implementation of the operation interceptor for trapping calls to services if required.
 *
 * @author matthew
 *
 */
abstract class ServiceInterceptor extends SerialisableObject {

    private $service;
    private $method;
    private $stop;

    /**
     * Get the service which this interceptor relates to.
     *
     * @return the $service
     */
    public function getService() {
        return $this->service;
    }

    /**
     * Set the service which this interceptor relates to.
     *
     * @param $service the $service to set
     */
    public function setService($service) {
        $this->service = $service;
    }

    /**
     * @return the $method
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param $method the $method to set
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * @return the $stop
     */
    public function getStop() {
        return $this->stop;
    }

    /**
     * @param $stop the $stop to set
     */
    public function setStop($stop) {
        $this->stop = $stop;
    }

    /**
     * Interceptor method called before a method is called on a service
     *
     * @param $methodName
     * @param $methodParameters
     */
    public abstract function beforeMethodCall($methodName, $methodParameters, $serviceInstance);

}

?>
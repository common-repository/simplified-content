<?php

include_once OOA_CORE_ROOT . "/mvc/exception/InvalidServiceInterceptorException.php";
include_once OOA_CORE_ROOT . "/transfer/xml/XMLToObjectConverter.php";
include_once OOA_CORE_ROOT . "/common/businessobjects/SerialisableObject.php";

/**
 * Worker class to evaluate any Service interceptors which are defined for a given Service.
 *
 * @author matthew
 *
 */
class ServiceInterceptorEvaluator extends SerialisableObject {

    private $defaultLocation = "businessobjects";
    private $interceptors = array();
    private static $instance;

    /**
     * @return the $defaultLocation
     */
    public function getDefaultLocation() {
        return $this->defaultLocation;
    }

    /**
     * @param $defaultLocation the $defaultLocation to set
     */
    public function setDefaultLocation($defaultLocation) {
        $this->defaultLocation = $defaultLocation;

        // Make sure we have included all possible files in the default location.
        $iterator = new DirectoryIterator ($defaultLocation);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if ($fileInfo->isFile()) {
                include_once $fileInfo->getPathname();
            }
        }

    }

    /**
     * Construct an evaluator statically from a configuration file.
     *
     * @param string $configFile
     * @return ServiceInterceptorEvaluator
     */
    public static function getDefaultInstance($forceReload = false) {
        if ($forceReload || !ServiceInterceptorEvaluator::$instance) {

            ServiceInterceptorEvaluator::$instance = new ServiceInterceptorEvaluator ();

            foreach (MVCSourceBaseManager::instance()->getSourceBases() as $sourceBase) {


                if (file_exists($sourceBase . "/config/service-interceptors.xml")) {
                    $converter =
                        new XMLToObjectConverter (array("ServiceInterceptors" => "ServiceInterceptorEvaluator"));

                    $newInterceptors =
                        $converter->convert(file_get_contents($sourceBase . "/config/service-interceptors.xml"));

                    // Merge interceptors from config file.
                    ServiceInterceptorEvaluator::$instance->interceptors =
                        array_merge(ServiceInterceptorEvaluator::$instance->interceptors, $newInterceptors->interceptors);

                }
            }


        }

        return ServiceInterceptorEvaluator::$instance;
    }

    /**
     * @return the $interceptors
     */
    public function getInterceptors() {
        return $this->interceptors;
    }

    /**
     * @param $interceptors the $interceptors to set
     */
    public function setInterceptors($interceptors) {

        // Handle single objects (convert to arrays)
        if ($interceptors && !is_array($interceptors)) {
            $interceptors = array($interceptors);
        }

        foreach ($interceptors as $interceptor) {
            if (!($interceptor instanceof ServiceInterceptor)) throw new InvalidServiceInterceptorException (get_class($interceptor));
        }

        $this->interceptors = $interceptors;
    }

    /**
     * Evaluate all interceptors defined for a particular controller passed in by name.
     * Return a boolean indicating whether all were successful or not.
     *
     * @param string $ServiceName
     * @return boolean
     */
    public function evaluateInterceptorsForService($serviceInstance, $method = null, $params = null) {

        // Return if no interceptors defined.
        if (!$this->interceptors) return true;

        $serviceName = is_string($serviceInstance) ? $serviceInstance : get_class($serviceInstance);

        foreach ($this->interceptors as $interceptor) {
            if ($interceptor->getService() == "*" || ($interceptor->getService() == $serviceName)) {

                if (!$interceptor->getMethod() || ($method == $interceptor->getMethod())) {
                    $result = $interceptor->beforeMethodCall($method, $params, $serviceInstance);
                    if (!$result) return false; else if ($interceptor->getStop()) return true;
                }

            }

        }
        return true;
    }

}

?>
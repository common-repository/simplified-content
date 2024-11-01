<?php

include_once OOA_CORE_ROOT . "/transfer/framework/ServiceInterceptorEvaluator.php";
include_once OOA_CORE_ROOT . "/mvc/exception/ServiceVetoedException.php";
include_once OOA_CORE_ROOT . "/common/util/ArrayUtils.php";
include_once OOA_CORE_ROOT . "/mvc/framework/MVCSourceBaseManager.php";
include_once OOA_CORE_ROOT . "/transfer/framework/FormatToObjectConverter.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ObjectToFormatConverter.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ServiceException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ServiceDescriptor.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/NoServiceSuppliedException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/NoServiceMethodSuppliedException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceNotConstructableException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceNotFoundException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceMethodNotFoundException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/TooFewServiceMethodParametersException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceRuntimeException.php";

class ServiceMethodRunner {

    private $serviceName = null;

    /**
     * Construct with the service name up front
     *
     * @param $serviceName string
     */
    public function ServiceMethodRunner($serviceName) {

        $this->serviceName = $serviceName;

        // If the class has not yet been loaded, attempt to locate it in a
        // services folder in MVCSourcePaths
        if (!class_exists($serviceName)) {
            $filename = MVCSourceBaseManager::resolvePath("services/" . $serviceName . ".php");
            if (file_exists($filename)) {
                include_once ($filename);
            }
        }

    }

    /**
     * Execute a service method using the passed parameters.
     *
     *
     * @param $serviceName string
     * @param $methodName string
     * @param $parameterArray array
     *
     * @throws ServiceNotFoundException
     * @throws ServiceMethodNotFoundException
     * @throws TooFewServiceMethodParametersException
     * @throws ServiceVetoedException
     */
    public function  execute($methodName, $parameterArray = array()) {

        // Service name
        $serviceName = $this->serviceName;

        // If we have a valid service class, but no method passed return a
        // service descriptor encoded accordingly.
        if (class_exists($serviceName) && !$methodName) {
            return new ServiceDescriptor ($serviceName);
        }

        // Try and construct a service instance using the service name
        try {
            $serviceInstance = ClassUtils::createNewClassInstance($serviceName);

        } catch (ClassNotFoundException $e) {
            throw new ServiceNotFoundException ($serviceName);
        } catch (ClassNotFoundException $e) {
            throw new ServiceNotFoundException ($serviceName);
        }

        // Now inspect the class for the method being accessed
        $reflectionClass = new ReflectionClass ($serviceName);

        // Throw if no method found on the service.
        if (!$reflectionClass->hasMethod($methodName)) {
            throw new ServiceMethodNotFoundException ($serviceName, $methodName);
        }

        $method = $reflectionClass->getMethod($methodName);

        // Get the supplied function parameters in either possible format
        // supplied.
        $suppliedParams = $this->getSuppliedFunctionParameters($parameterArray, $method);

        // Now find out how many are required
        $paramsSupplied = sizeof($suppliedParams);
        $paramsRequired = $method->getNumberOfRequiredParameters();

        if ($paramsSupplied < $paramsRequired) {
            throw new TooFewServiceMethodParametersException ($serviceName, $methodName, $paramsSupplied, $paramsRequired);
        }

        // Call any interceptors for this service
        $interceptorResult = ServiceInterceptorEvaluator::getDefaultInstance()->evaluateInterceptorsForService($serviceInstance, $methodName, $suppliedParams);

        if ($interceptorResult === true) {

            // Otherwise actually invoke the method and capture the result
            $result = $method->invokeArgs($serviceInstance, $suppliedParams);

            // Now encode the result using the object to format encoder before
            // returning
            return $result;

        } else {
            throw new ServiceVetoedException ($serviceName);
        }
    }

    /**
     * Get the function parameters by hook or by crook.
     * We allow parameters to be either supplied with keys in the format param1,
     * param2, param3.....paramN which represent the params in that order.
     * Alternatively, parameters can be supplied with keys that actually match
     * the names of the function parameters using reflection. This is great for
     * Constrained JSON calls etc.
     */
    private function getSuppliedFunctionParameters($parameterArray, $method) {

        // Check that we have any parameters to sort first of all
        if (sizeof($parameterArray) > 0) {

            // Now see if any were supplied in order sequence format. If so,
            // sort and return these
            // Otherwise, loop through all of the parameters in the method,
            // looking for parameters
            // which match the names in the passed array. If any are found, add
            // these at the right location
            $orderedParams = ArrayUtils::getAllArrayItemsByKeyPrefix($parameterArray, "param");

            // Check they are all numeric
            $checkedParams = array();
            foreach ($orderedParams as $paramKey => $paramValue) {
                if (preg_match("/param[0-9]+/", $paramKey) == 1)
                    $checkedParams[$paramKey] = $paramValue;
            }

            if (sizeof($checkedParams) > 0) {
                ksort($checkedParams);
                return $checkedParams;
            } else {
                $nameBasedParams = array();
                foreach ($method->getParameters() as $methodParam) {
                    if (array_key_exists($methodParam->getName(), $parameterArray)) {
                        $paramValue = $parameterArray [$methodParam->getName()];
                        $nameBasedParams [$methodParam->getPosition()] = $paramValue === null ? $methodParam->getDefaultValue() : $paramValue;
                    } else {
                        $nameBasedParams [$methodParam->getPosition()] = null;
                    }
                }
                return $nameBasedParams;
            }

        } else {
            return array();
        }

    }

}

?>
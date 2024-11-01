<?php

include_once OOA_CORE_ROOT . "/transfer/framework/ServiceInterceptorEvaluator.php";
include_once OOA_CORE_ROOT . "/mvc/exception/ServiceVetoedException.php";
include_once OOA_CORE_ROOT . "/common/util/ArrayUtils.php";
include_once OOA_CORE_ROOT . "/transfer/framework/FormatToObjectConverter.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ObjectToFormatConverter.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ServiceException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ServiceDescriptor.php";
include_once OOA_CORE_ROOT . "/transfer/framework/ServiceMethodRunner.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/NoServiceSuppliedException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/NoServiceMethodSuppliedException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceNotConstructableException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceNotFoundException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceMethodNotFoundException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/TooFewServiceMethodParametersException.php";
include_once OOA_CORE_ROOT . "/transfer/framework/exception/ServiceRuntimeException.php";

/**
 * Dispatcher object called directly from format controllers to invoke methods
 * on services using the passed url and request parameters within
 * particular formats.
 *
 * Known formats are JSON, XML and CSV.
 *
 * This dispatcher supports multiple conversion formats, and is constructed with
 * a FormatToObject converter and ObjectToFormat converter
 * in order to perform both way conversions as required.
 *
 * Service classes are looked for in the MVC source bases under the "services"
 * sub folders.
 *
 * A service call takes the following highly descriptive URL form
 *
 * http://SERVERNAME/serviceclass/methodname?param1=1&param2=3
 *
 * This would look for a service class called the first fragment and with a
 * method called the second fragment, which it would then attempt
 * to invoke with the parameters supplied as query parameters in the order
 * param1, param2 etc. Both HTTP GET and POST are valid for supplying
 * parameters.
 *
 * The format of the passed parameters should match the conversion format
 *
 * @author mark
 *
 */
class ServiceDispatcher {

    private $objectToFormatConverter;
    private $formatToObjectConverter;

    /**
     * Construct with both types of converter for a given format.
     * This enables us to convert
     * incoming request parameters into PHP objects using the format to object
     * converter for passing to the service
     * and then return the results from the service call in the given format
     * using the object to format converter.
     *
     * @param $objectToFormatConverter ObjectToFormatConverter
     * @param $formatToObjectConverter FormatToObjectConverter
     */
    public function ServiceDispatcher($objectToFormatConverter = null, $formatToObjectConverter = null) {
        $this->objectToFormatConverter = $objectToFormatConverter;
        $this->formatToObjectConverter = $formatToObjectConverter;
    }

    /**
     * Dispatch a request to the service represented by the passed URL and
     * optionally
     * the array of request parameters in which can be found param1, param2 etc.
     *
     * @param
     *         $url
     * @param
     *         $requestParams
     */
    public function dispatchRequest($url, $parameterArray = array()) {

        $serviceName = "";
        $serviceMethod = "";
        try {

            // Firstly, do the basic checks to see whether we received enough
            // parameters before continuing
            $urlHelper = new URLHelper ($url);

            // If we only have a single controller segment, throw no service
            // supplied
            if ($urlHelper->getSegmentCount() < 2) {
                throw new NoServiceSuppliedException ($url);
            }

            // Read the service name and service method if it exists.
            $serviceName = $urlHelper->getSegment(1);
            $serviceMethod = $urlHelper->getSegmentCount() >= 3 ? $urlHelper->getSegment(2) : null;

            // Use a service method runner to execute the method for the input
            // parameters
            $serviceMethodRunner = new ServiceMethodRunner ($serviceName);

            // Encode the params using the format to object converter
            $encodedParams = array();
            foreach ($parameterArray as $paramKey => $param) {

                // Strip non alphas from the string
                $alphaOnlyParam = preg_replace("/[a-zA-Z0-9_\s,']/", "", $param);
                if ($alphaOnlyParam && $this->formatToObjectConverter) {
                    $encodedParams [$paramKey] = $this->formatToObjectConverter->convert($param);
                } else {
                    $encodedParams [$paramKey] = $param;
                }
            }


            // Execute the method and return the converted result.
            $result = $serviceMethodRunner->execute($serviceMethod, $encodedParams);
            return $result === null ? null : ($this->objectToFormatConverter ? $this->objectToFormatConverter->convert($result) : $result);

        } // Wrap all exceptions in a service runtime exception for returning in a
            // serialised manner back to the client.
        catch (Exception $e) {
            $exceptionObject = new ServiceException ($serviceName, $serviceMethod, $e);
            throw new ServiceRuntimeException ($this->objectToFormatConverter ? $this->objectToFormatConverter->convert($exceptionObject) : $exceptionObject);
        }

    }

}

?>
<?php

include_once OOA_CORE_ROOT . "/transfer/json/JSONToObjectConverter.php";
include_once OOA_CORE_ROOT . "/transfer/json/ObjectToJSONConverter.php";


class OOAWPServiceDispatcher {


    private $jsonToObjectConverter;
    private $objectToJSONConverter;

    public function OOAWPServiceDispatcher() {
        $this->jsonToObjectConverter = new JSONToObjectConverter();
        $this->objectToJSONConverter = new ObjectToJSONConverter();
    }

    /**
     * Internal function bound to wordpress
     *
     * @return [type] [description]
     */
    public function dispatchRequest() {

        $pluginName = isset($_REQUEST["plugin"]) ? $this->decodeParameter($_REQUEST["plugin"]) : null;
        $service = isset($_REQUEST["service"]) ? $this->decodeParameter($_REQUEST["service"]) : null;
        $serviceMethod = isset($_REQUEST["serviceMethod"]) ? $this->decodeParameter($_REQUEST["serviceMethod"]) : null;

        if (!$pluginName || !$service || !$serviceMethod) {
            $message = "You must supply a plugin name, service and service method when using the service dispatcher";
            Logger::log($message);
            echo $message;
            die();
        }

        // Prepare the parameters for calling
        $parameters = $_REQUEST;
        unset ($parameters["action"]);
        unset ($parameters["plugin"]);
        unset ($parameters["service"]);
        unset ($parameters["serviceMethod"]);


        // Attempt to find the service with the name inside the plugin
        $serviceClassFile = WP_PLUGIN_DIR . "/" . $pluginName . "/services/" . $service . ".php";
        if (file_exists($serviceClassFile)) {

            // Include the class file
            include_once($serviceClassFile);

            // Create an instance
            $serviceInstance = new $service();

            // Check that the method exists
            if (method_exists($serviceInstance, $serviceMethod)) {

                $serviceClass = new ReflectionClass($serviceInstance);
                $serviceMethodInstance = $serviceClass->getMethod($serviceMethod);
                $methodParameters = $serviceMethodInstance->getParameters();

                $args = array();
                foreach ($methodParameters as $methodParam) {
                    if (isset($parameters[$methodParam->getName()])) {

                        $param = $parameters[$methodParam->getName()];
                        $args[] = $this->decodeParameter($param);
                    } else {
                        $args[] = null;
                    }
                }


                $result = $serviceMethodInstance->invokeArgs($serviceInstance, $args);


                if ($result !== null) {
                    if ($result instanceof ServiceException) {
                        header("HTTP/1.0 500 Internal Server Error");
                    }

                    echo $this->objectToJSONConverter->convert($result);

                }

                die();

            } else {
                $message = "The service method " . $serviceMethod . " cannot be found on service " . $service;
                Logger::log($message);
                echo $message;
                die();
            }


        } else {
            $message = "The service class cannot be found for service " . $service;
            Logger::log($message);
            echo $message;
            die();
        }


    }


    // Decode a single parameter value
    private function decodeParameter($paramValue) {

        $newParamValue = urldecode($paramValue);
        $newParamValue = str_replace('\\"', '"', $newParamValue);
        $newParamValue = str_replace("\\'", "'", $newParamValue);

        $converted = $this->jsonToObjectConverter->convert($newParamValue);
        return $converted === NULL ? $newParamValue : $converted;
    }


}


?>
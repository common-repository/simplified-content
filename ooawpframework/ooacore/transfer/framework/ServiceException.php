<?php

include_once OOA_CORE_ROOT . "/common/businessobjects/SerialisableObject.php";
include_once OOA_CORE_ROOT . "/common/businessobjects/DynamicSerialisableObject.php";


/**
 * NB: This is not really an EXCEPTION but a class called exception to act as a return value from a remote service.
 *
 * Generic serialisable exception return value which is returned from the service dispatcher if an exception occurs during service execution.
 *
 * @author mark
 *
 */
class ServiceException extends DynamicSerialisableObject {

    protected $serviceName;
    protected $serviceMethod;
    protected $exceptionClass;
    protected $exceptionCode;
    protected $exceptionMessage;
    protected $exceptionLineNumber;
    protected $exceptionFile;
    protected $exceptionTrace;

    /**
     * Construct this wrapped exception using an underlying exception
     *
     * @param Exception $exception
     */
    public function ServiceException($serviceName = null, $serviceMethod = null, $exception = null) {

        parent::DynamicSerialisableObject(false);

        $this->serviceName = $serviceName;
        $this->serviceMethod = $serviceMethod;

        if ($exception) {

            $this->exceptionClass = get_class($exception);
            $this->exceptionCode = $exception->getCode();
            $this->exceptionMessage = $exception->getMessage();
            $this->exceptionLineNumber = $exception->getLine();
            $this->exceptionFile = $exception->getFile();
            $this->exceptionTrace = $exception->getTraceAsString();

            $exceptionClass = new ReflectionClass($exception);
            $methods = $exceptionClass->getMethods();
            foreach ($methods as $method) {
                if ($method->isUserDefined() && substr($method->getName(), 0, 3) == "get") {
                    $property = strtolower(substr($method->getName(), 3, 1)) . substr($method->getName(), 4);
                    $this->__setSerialisablePropertyValue($property, $method->invoke($exception));
                }
            }

        }

    }

    /**
     * @return the $exceptionClass
     */
    public function getExceptionClass() {
        return $this->exceptionClass;
    }

    /**
     * @return the $exceptionCode
     */
    public function getExceptionCode() {
        return $this->exceptionCode;
    }

    /**
     * @return the $exceptionMessage
     */
    public function getExceptionMessage() {
        return $this->exceptionMessage;
    }

    /**
     * @return the $exceptionLineNumber
     */
    public function getExceptionLineNumber() {
        return $this->exceptionLineNumber;
    }

    /**
     * @return the $exceptionFile
     */
    public function getExceptionFile() {
        return $this->exceptionFile;
    }

    public function getExceptionTrace() {
        return $this->exceptionTrace;
    }

    /**
     * @return the $serviceName
     */
    public function getServiceName() {
        return $this->serviceName;
    }

    /**
     * @return the $serviceMethod
     */
    public function getServiceMethod() {
        return $this->serviceMethod;
    }

}

?>
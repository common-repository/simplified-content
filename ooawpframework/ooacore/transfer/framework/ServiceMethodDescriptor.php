<?php

/**
 * Descriptor which enumerates the name and parameters for a given service method.
 *
 * @author mark
 *
 */
class ServiceMethodDescriptor extends SerialisableObject {

    protected $methodName;
    protected $parameters;

    /**
     * Construct this method descriptor with a reflection method object
     *
     * @param ReflectionMethod $methodReflectionObject
     */
    public function ServiceMethodDescriptor($methodReflectionObject = null) {

        if ($methodReflectionObject) {

            $this->methodName = $methodReflectionObject->getName();
            $methodParams = array();
            foreach ($methodReflectionObject->getParameters() as $parameter) {
                $methodParams [] = $parameter->getName();
            }

            $this->parameters = $methodParams;
        }
    }

    /**
     * @return the $methodName
     */
    public function getMethodName() {
        return $this->methodName;
    }

    /**
     * @return the $parameters
     */
    public function getParameters() {
        return $this->parameters;
    }

}

?>
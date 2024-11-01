<?php

include_once OOA_CORE_ROOT . "/common/exception/ClassNotFoundException.php";
include_once OOA_CORE_ROOT . "/common/exception/ClassNotConstructableException.php";
include_once OOA_CORE_ROOT . "/common/exception/ClassNotSerialisableException.php";

/**
 * Helper utility methods when working with classes.
 *
 * @author mark
 *
 */
class ClassUtils {


    private static $seenClasses = array();

    /**
     * Check to see whether or not a given child class extends a given parent class
     *
     * @param $childClassName
     * @param $parentClassName
     */
    public static function doesClassExtendClass($childClassName, $parentClassName) {

        $reflectionClass = new ReflectionClass ($childClassName);

        do {
            if ($reflectionClass->getName() == $parentClassName)
                return true;
            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass);

        // Assume false if we get this far
        return false;
    }

    /**
     * Centralised utility for creating new class instances using the blank constructor.
     * This throws appropriate exceptions if the class does not exist or if the class is not
     * constructable (i.e. has no blank constructor) .  If the include location is passed, the class will be included
     * from there if required.
     */
    public static function createNewClassInstance($className, $includeLocation = null, $requireSerialisable = false) {

        // Optimise out class creation as this helps for large data sets
        if (isset(ClassUtils::$seenClasses[$className . $includeLocation . $requireSerialisable])) {
            return new $className();
        } else {

            // If no class exists but we have an include location, include it now...
            if (!class_exists($className)) {

                if ($includeLocation) {
                    include_once $includeLocation . "/" . $className . ".php";

                    // Bail out if class hasn't been loaded
                    if (!class_exists($className)) {
                        throw new ClassNotFoundException ($className);
                    }
                } else {
                    throw new ClassNotFoundException ($className);
                }
            }


            // Check that we can construct it using the blank constructor or bail out.
            $reflectionClass = new ReflectionClass ($className);
            if ($reflectionClass->getConstructor() && $reflectionClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                throw new ClassNotConstructableException ($className);
            }

            // Make the instance
            $newInstance = new $className ();

            // If we are checking for serialisability as well, do this now
            if ($requireSerialisable && !($newInstance instanceof SerialisableObject)) {
                throw new ClassNotSerialisableException ($className);
            }

            ClassUtils::$seenClasses[$className . $includeLocation . $requireSerialisable] = 1;

            // If all is well, return.
            return $newInstance;

        }

    }

}

?>
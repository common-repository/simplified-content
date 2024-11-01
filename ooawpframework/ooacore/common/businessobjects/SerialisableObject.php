<?php

include_once OOA_CORE_ROOT . "/common/exception/PropertyNotReadableException.php";
include_once OOA_CORE_ROOT . "/common/exception/PropertyNotWritableException.php";
include_once OOA_CORE_ROOT . "/common/util/PropertyNameNormaliser.php";
include_once OOA_CORE_ROOT . "/common/exception/ClassNotSerialisableException.php";
include_once OOA_CORE_ROOT . "/common/businessobjects/AssociativeArray.php";

/**
 * Base serialisable object class.  This is the base class for any business objects which participate in any of the core OOA persistence
 * libraries as well as data transfer objects used by the transport libraries.
 *
 * @author mark
 * @package ooacore,common
 */
class SerialisableObject {

    /**
     * Store for efficiency purposes the accessor maps for every serialisable type encountered once.
     *
     * @var array
     */
    private static $__accessorMaps;

    /**
     * Get a serialisable property value by name if it exists.  Throw an exception if it doesn't exist.
     *
     * @param string $propertyName
     */
    public function __getSerialisablePropertyValue($propertyName) {
        $map = $this->__getSerialisablePropertyMap();
        if (array_key_exists($propertyName, $map)) {
            return $map [$propertyName];
        } elseif (array_key_exists(substr(strtoupper($propertyName), 0, 1) . substr($propertyName, 1), $map)) {
            return $map [substr(strtoupper($propertyName), 0, 1) . substr($propertyName, 1)];
        } else {
            throw new PropertyNotReadableException (get_class($this), $propertyName);
        }
    }

    /**
     * Set a serialisable property by name if it exists.  Throw an exception if it doesn't exist.
     *
     * @param string $propertyName
     * @param mixed $newValue
     */
    public function __setSerialisablePropertyValue($propertyName, $newValue) {
        $this->__setSerialisablePropertyMap(array($propertyName => $newValue));
    }

    /**
     * Get an associative array of all of the serialisable properties defined on this class.
     */
    public function __getSerialisablePropertyMap() {
        $propertyAccessors = $this->__findSerialisablePropertyAccessors();
        $propertyMap = array();
        foreach ($propertyAccessors as $accessorSet) {

            // If a get accessor exists, add it to the map
            if (isset ($accessorSet ["get"])) {
                $accessor = $accessorSet ["get"];
                if ($accessor instanceof ReflectionMethod) {
                    $propertyName = substr($accessor->getName(), 3);
                    $propertyName = PropertyNameNormaliser::instance()->normalisePropertyName($propertyName);
                    if ($accessor->isPublic())
                        $propertyMap [$propertyName] = $accessor->invoke($this);
                    else if ($accessor->isProtected()) {
                        $methodName = "get" . $propertyName;
                        $propertyMap [$propertyName] = $this->$methodName ();
                    }
                } else if ($accessor instanceof ReflectionProperty) {
                    $propertyName = $accessor->getName();
                    if ($accessor->isPublic())
                        $propertyMap [$propertyName] = $accessor->getValue($this);
                    else if ($accessor->isProtected()) {
                        $propertyName = $accessor->getName();
                        $propertyMap [$propertyName] = $this->$propertyName;
                    }
                }
            }

        }

        return $propertyMap;

    }

    /**
     * Set an associative array of serialisable properties
     */
    public function __setSerialisablePropertyMap($propertyMap, $ignoreNoneWritableProperties = false) {
        $propertyAccessors = $this->__findSerialisablePropertyAccessors();

        $noneWritables = array();
        foreach ($propertyMap as $inputPropertyName => $propertyValue) {
            $propertyName = strtolower($inputPropertyName);

            // If we get a match, call the appropriate function
            if (isset ($propertyAccessors [$propertyName]) && isset ($propertyAccessors [$propertyName] ["set"])) {
                $accessor = $propertyAccessors [$propertyName] ["set"];
                if ($accessor instanceof ReflectionMethod) {
                    if ($accessor->isPublic())
                        $accessor->invoke($this, $propertyValue);
                    else if ($accessor->isProtected()) {
                        $methodName = "set" . $propertyName;
                        $this->$methodName ($propertyValue);
                    } else {
                        if (!$ignoreNoneWritableProperties)
                            throw new PropertyNotWritableException (get_class($this), $propertyName);
                    }
                } else if ($accessor instanceof ReflectionProperty) {
                    if ($accessor->isPublic())
                        $accessor->setValue($this, $propertyValue);
                    else if ($accessor->isProtected()) {
                        $propertyName = $accessor->getName();
                        $this->$propertyName = $propertyValue;
                    } else {
                        if (!$ignoreNoneWritableProperties)
                            throw new PropertyNotWritableException (get_class($this), $propertyName);
                    }
                }
            } else {
                // If none writable properties ignored, instead add them to an array for return for processing
                // higher if required, else throw exceptions.
                if ($ignoreNoneWritableProperties) {
                    $noneWritables [$inputPropertyName] = $propertyValue;
                } else {
                    throw new PropertyNotWritableException (get_class($this), $propertyName);
                }
            }

        }

        return $noneWritables;
    }

    /**
     * Get a clone of an object
     */
    public function __clone() {
        return unserialize(serialize($this));
    }


    /**
     * Find all serialisable property objects, return a map of accessor objects keyed in by GET and SET.
     */
    protected function __findSerialisablePropertyAccessors() {

        // Grab the class name first.
        $className = get_class($this);

        // If no accessor map has been previously cached for this class type, cache it now.
        if (!isset (SerialisableObject::$__accessorMaps [$className])) {


            $reflectionClass = new ReflectionClass ($className);

            // Create the accessors array for storing all possible accessors.
            $accessors = array();

            // Loop through all methods, checking for public get and set accessors first.
            $methods = $reflectionClass->getMethods();
            foreach ($methods as $method) {

                if ($method->isStatic())
                    continue;

                if ((substr($method->getName(), 0, 3) == "get") && ($method->getNumberOfRequiredParameters() == 0)) {
                    $propertyName = strtolower(substr($method->getName(), 3));
                    if (!isset ($accessors [$propertyName])) {
                        $accessors [$propertyName] = array();
                    }
                    $accessors [$propertyName] ["get"] = $method;
                } else if (substr($method->getName(), 0, 3) == "set") {
                    $propertyName = strtolower(substr($method->getName(), 3));
                    if (!isset ($accessors [$propertyName])) {
                        $accessors [$propertyName] = array();
                    }
                    $accessors [$propertyName] ["set"] = $method;
                }
            }

            // Now loop through all properties, checking for any public / protected ones
            $properties = $reflectionClass->getProperties();
            foreach ($properties as $property) {

                if ($property->isStatic())
                    continue;

                $propertyName = strtolower($property->getName());

                if (!isset ($accessors [$propertyName])) {
                    $accessors [$propertyName] = array();
                }
                if (!isset ($accessors [$propertyName] ["get"]))
                    $accessors [$propertyName] ["get"] = $property;
                if (!isset ($accessors [$propertyName] ["set"]))
                    $accessors [$propertyName] ["set"] = $property;

            }

            SerialisableObject::$__accessorMaps [$className] = $accessors;

        }

        return SerialisableObject::$__accessorMaps [$className];
    }


    /**
     * Get an array of member values for a given member for the array of objects passed
     * using the same indexing system as the passed objects.
     *
     * @static
     * @param $member
     * @param $objects
     */
    public
    static function getMemberValueArrayForObjects($member, $objects) {

        $returnValues = array();

        foreach ($objects as $key => $value) {

            if ($value instanceof SerialisableObject) {
                $returnValues[$key] = $value->__getSerialisablePropertyValue($member);
            } else {
                throw new ClassNotSerialisableException(get_class($value));
            }
        }

        return $returnValues;

    }


    /**
     * Index the array of passed objects by the supplied member, returning an associative array.
     *
     * @param $member
     * @param $objects
     */
    public static function indexArrayOfObjectsByMember($member, $objects) {

        $returnValues = array();

        foreach ($objects as $object) {
            if ($object instanceof SerialisableObject) {
                $returnValues[$object->__getSerialisablePropertyValue($member)] = $object;
            } else {
                throw new ClassNotSerialisableException(get_class($object));
            }
        }

        return $returnValues;

    }


    /**
     * Filter an array of objects by a specified member.  Perhaps in the future extend
     * to multiple match types.
     *
     * @param $member
     * @param $objects
     * @param $filterValue
     */
    public static function filterArrayOfObjectsByMember($member, $objects, $filterValue) {

        $filteredObjects = array();

        foreach ($objects as $object) {
            if ($object instanceof SerialisableObject) {
                if ($filterValue == $object->__getSerialisablePropertyValue($member))
                    $filteredObjects[] = $object;
            } else {
                throw new ClassNotSerialisableException(get_class($object));
            }
        }

        return $filteredObjects;

    }


    /**
     * Group an array of objects by a given member.
     *
     * @param $member
     * @param $objects
     */
    public static function groupArrayOfObjectsByMember($member, $objects) {

        if (!is_array($member))
            $member = array($member);

        $leafMember = array_pop($member);


        $groupedObjects = new AssociativeArray();

        foreach ($objects as $object) {

            $rootNode = $groupedObjects;
            foreach ($member as $memberComponent) {
                $groupValue = $object->__getSerialisablePropertyValue($memberComponent);
                if (!$groupValue) $groupValue = "NULL";

                if (!isset($rootNode[$groupValue]))
                    $rootNode[$groupValue] = new AssociativeArray();

                $rootNode = $rootNode[$groupValue];
            }

            $leafValue = $object->__getSerialisablePropertyValue($leafMember);
            if (!$leafValue) $leafValue = "NULL";

            if (!$rootNode[$leafValue])
                $rootNode[$leafValue] = array();

            $leafValues = $rootNode[$leafValue];
            $leafValues[] = $object;
            $rootNode[$leafValue] = $leafValues;
        }

        return $groupedObjects->toArray();
    }


}

?>
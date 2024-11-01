<?php

include_once OOA_CORE_ROOT . "/common/exception/NoneExistentMethodException.php";
include_once OOA_CORE_ROOT . "/common/exception/NoneAssociativeArrayAccessException.php";
include_once OOA_CORE_ROOT . "/common/businessobjects/SerialisableObject.php";

/**
 * Dynamic serialisable object.  Provides a mechanism for creating dynamically extensible objects and indeed fully dynamic objects (in non-strict) mode if so desired.
 *
 * @author mark
 *
 */
class DynamicSerialisableObject extends SerialisableObject implements ArrayAccess, Iterator {

    private $__strictMode;
    private $__dynamicPropertyKeys = array();
    private $__dynamicPropertyMap = array();

    private $iteratorPosition = 0;

   
    /**
     * Construct this object with a strict mode indicator flag.  In strict mode, any dynamic properties must be supplied in a call
     * to __setDynamicPropertyKeys.  Otherwise in non strict mode, any properties may be set and got on this class with no checking.
     *
     * @param boolean $strictMode
     */
    public function DynamicSerialisableObject($strictMode = true) {
        $this->__strictMode = $strictMode;
    }

    /**
     * Set the array of dynamic property keys permissable for mapping in strict mode.
     *
     * @param array $keys
     */
    public function __setDynamicPropertyKeys($keys) {
        $this->__dynamicPropertyKeys = $keys;
    }


    /**
     * Get a dynamic property (fast lookup method)
     *
     * @param $propertyKey
     * @return mixed
     */
    public function __getDynamicProperty($propertyKey) {
        return isset($this->__dynamicPropertyMap[$propertyKey]) ? $this->__dynamicPropertyMap[$propertyKey] : null;
    }

    /**
     * Implement the magic __call method to allow us to intercept calls for getters and setters to undefined functions.
     *
     * @param $methodName
     * @param $args
     */
    public function __call($methodName, $args) {

        if (strlen($methodName) > 3) {
            $propertyName = PropertyNameNormaliser::instance()->normalisePropertyName(substr($methodName, 3));
            if (strtolower(substr($methodName, 0, 3) == "set") && sizeof($args) == 1) {
                if (!$this->__strictMode || is_numeric(array_search($propertyName, $this->__dynamicPropertyKeys)))
                    $this->__dynamicPropertyMap [$propertyName] = $args [0];
                else
                    throw new PropertyNotWritableException (get_class($this), $propertyName);
                return;
            } else if (strtolower(substr($methodName, 0, 3) == "get") && sizeof($args) == 0) {
                if (!$this->__strictMode || is_numeric(array_search($propertyName, $this->__dynamicPropertyKeys)))
                    return array_key_exists($propertyName, $this->__dynamicPropertyMap) ? $this->__dynamicPropertyMap [$propertyName] : null;
                else
                    throw new PropertyNotReadableException (get_class($this), $propertyName);
            }
        }

        // If we haven't returned yet, we throw none existent method.
        throw new NoneExistentMethodException (get_class($this), $methodName);

    }

    /**
     * Intercept setters for undefined properties
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function __set($propertyName, $propertyValue) {
        if (!$this->__strictMode || is_numeric(array_search($propertyName, $this->__dynamicPropertyKeys)))
            if ($propertyValue !== null) {
                $this->__dynamicPropertyMap [$propertyName] = $propertyValue;
            } else {
                unset($this->__dynamicPropertyMap[$propertyName]);
            }
        else
            throw new PropertyNotWritableException (get_class($this), $propertyName);
    }

    /**
     * Intercept getters for undefined properties
     *
     * @param string $propertyName
     */
    public function __get($propertyName) {
        if (!$this->__strictMode || is_numeric(array_search($propertyName, $this->__dynamicPropertyKeys)))
            return isset ($this->__dynamicPropertyMap [$propertyName]) ? $this->__dynamicPropertyMap [$propertyName] : null;
        else
            throw new PropertyNotReadableException (get_class($this), $propertyName);
    }

    /**
     * Override the parent get property value method to be a little more tolerant than the parent
     *
     * @param string $propertyName
     */
    public function __getSerialisablePropertyValue($propertyName) {
        try {
            return parent::__getSerialisablePropertyValue($propertyName);
        } catch (PropertyNotReadableException $e) {
            if ($this->__strictMode) {
                throw $e;
            } else {
                return null;
            }
        }
    }

    /**
     * Get the array of serialisable properties
     *
     * @return array
     */
    public function __getSerialisablePropertyMap() {

        $serialisableProperties = parent::__getSerialisablePropertyMap();

        // Merge our dynamic properties in and return.
        return array_merge($this->__dynamicPropertyMap, $serialisableProperties);

    }

    /**
     * Set an array of serialisable properties
     *
     *
     * @param array $propertyMap
     * @param boolean $ignoreNoneWritableProperties
     * @return array
     */
    public function __setSerialisablePropertyMap($propertyMap, $ignoreNoneWritableProperties = false) {


        // Always ignore any unknown properties in parent as we need to capture these for dynamic purposes
        $dynamicProperties = parent::__setSerialisablePropertyMap($propertyMap, true);

        // If none strict, simply merge all properties in the property map into the dynamic property map.
        if (!$this->__strictMode) {
            $this->__dynamicPropertyMap = array_merge($this->__dynamicPropertyMap, $dynamicProperties);
        } else {

            foreach ($dynamicProperties as $propertyName => $propertyValue) {
                try {
                    $this->__set($propertyName, $propertyValue);
                } catch (PropertyNotWritableException $e) {
                    if (!$ignoreNoneWritableProperties)
                        throw $e;
                }
            }

        }

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->__getSerialisablePropertyMap());
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function &offsetGet($offset) {

        if (isset($this->__dynamicPropertyMap[$offset])) {
            return $this->__dynamicPropertyMap [$offset];
        } else {
            return null;
        }

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value) {

        if (!$offset || is_numeric($offset))
            throw new NoneAssociativeArrayAccessException();
        $this->__set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset) {
        $this->__set($offset, null);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current() {
        $map = $this->__getSerialisablePropertyMap();
        $mapKeys = array_keys($map);
        return $map[$mapKeys[$this->iteratorPosition]];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next() {
        $this->iteratorPosition++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        $map = $this->__getSerialisablePropertyMap();
        $mapKeys = array_keys($map);
        return $mapKeys[$this->iteratorPosition];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid() {
        $map = $this->__getSerialisablePropertyMap();
        $mapKeys = array_keys($map);
        return isset($mapKeys[$this->iteratorPosition]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind() {
        $this->iteratorPosition = 0;
    }
}

?>
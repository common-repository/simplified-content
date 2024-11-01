<?php

include_once OOA_CORE_ROOT . "/common/businessobjects/DynamicSerialisableObject.php";

/**
 * Associative array explicit object.  This enables us to differentiate between numeric and associative arrays
 * especially when they are blank.  Particularly useful for transfer when serialising objects using JSON etc.
 *
 * Class AssociativeArray
 */
class AssociativeArray extends DynamicSerialisableObject {

    public function AssociativeArray() {
        parent::DynamicSerialisableObject(false);
    }


    /**
     * Convert this into a regular php array.
     */
    public function toArray() {

        // Get each property
        $array = array();
        foreach ($this->__getSerialisablePropertyMap() as $key => $value) {
            if ($value instanceof AssociativeArray) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }


} 
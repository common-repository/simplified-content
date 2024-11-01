<?php

include_once OOA_CORE_ROOT . "/transfer/framework/ObjectToFormatConverter.php";
include_once OOA_CORE_ROOT . "/common/exception/ClassNotSerialisableException.php";

/**
 * Converter which recursively converts Objects / Values to JSON.
 *
 */
class ObjectToJSONConverter implements ObjectToFormatConverter {

    /**
     * Convert a passed object to JSON notation
     *
     */
    public function convert($object) {

        // Normalise the input to array form.
        $object = $this->normaliseToArrayForm($object);

        // Then simply encode using PHP JSON libraries.
        return json_encode($object);
    }

    /**
     * Normalise an object / array into standard array form for JSON conversion.
     *
     * @param mixed $object
     */
    private function normaliseToArrayForm($object) {

        // If object input, deal with it now.
        if (is_object($object)) {

            $className = get_class($object);

            // Bail out if attempt to convert a none serialisable.
            if (!($object instanceof SerialisableObject)) {
                throw new ClassNotSerialisableException ($className);
            }

            // Initialise the return array with the className parameter as a minimum
            $returnValue = array("className" => $className);

            // Get all members from the object
            $allMemberMap = $object->__getSerialisablePropertyMap();

            // Loop through all members in the all member map, create
            foreach ($allMemberMap as $memberName => $memberValue) {

                // Camel case the name if not an associative array
                if (!$object instanceof AssociativeArray)
                    $modifiedMemberName = strtolower($memberName [0]) . substr($memberName, 1);
                else $modifiedMemberName = $memberName;

                $returnValue [$modifiedMemberName] = $this->normaliseToArrayForm($memberValue);
            }

        } // If array input, deal with it now.
        else if (is_array($object)) {
            $returnValue = array();
            foreach ($object as $key => $item) {
                $returnValue [$key] = $this->normaliseToArrayForm($item);
            }

        } // Otherwise leave the input intact
        else if (is_bool($object)) {
            $returnValue = $object;
        } else {
            $object = mb_detect_encoding($object, mb_detect_order(), true) === 'UTF-8' ? $object : mb_convert_encoding($object, 'UTF-8');

            if ($object && !json_encode($object)) {
                $returnValue = null;
            } else {
                $returnValue = $object;
            }
        }

        return $returnValue;

    }

}

?>
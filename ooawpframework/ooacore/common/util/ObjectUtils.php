<?php

/**
 * Miscellaneous utilities for interacting with objects.
 *
 * Class ObjectUtils
 */
class ObjectUtils {

    /**
     * Convenient function for evaluating the value for a nested member path on an object in dot / [] format.
     *
     * The is assumed to start from the root of the object
     *
     * e.g. for Member Path
     *
     * "name" would return the value of $object->getName() or $object->name or null if neither is defined.
     * "address.street1" would return the value of $object->getAddress()->getStreet1() or equivalent member access.
     * "address[1].phone[2].number would traverse the direct member "address" as an array followed by "phone" as an array
     * and return the number member.
     *
     *
     * @param $object
     * @param $memberPath
     */
    public static function getNestedObjectProperty($object, $memberPath) {

        // Explode the path on . firstly
        $explodedPath = explode(".", $memberPath);

        $currentObject = $object;

        while ($currentObject != null && sizeof($explodedPath) > 0) {

            $explodedMemberName = explode("[", array_shift($explodedPath));

            $memberName = $explodedMemberName[0];


            if (is_array($currentObject)) {
                if (isset($currentObject[$memberName])) {
                    $currentObject = &$currentObject[$memberName];
                } else {
                    $currentObject = null;
                }
            } else {

                $functionName = "get" . $memberName;

                if (method_exists($currentObject, $functionName)) {
                    $currentObject = $currentObject->$functionName();
                } else if (property_exists($currentObject, $memberName) || ($currentObject instanceof DynamicSerialisableObject)) {
                    $currentObject = $currentObject->$memberName;
                } else {
                    $currentObject = null;
                }
            }

            if ($currentObject && sizeof($explodedMemberName) > 1) {
                if (is_array($currentObject)) {
                    $offset = array_shift(explode("]", $explodedMemberName[1]));
                    if (isset($currentObject[$offset]))
                        $currentObject = &$currentObject[$offset];
                    else
                        $currentObject = null;
                } else {
                    $currentObject = null;
                }
            }


        }

        return $currentObject;

    }


    /**
     * Set a nested object property value on an object using a member path to locate it.
     *
     * @param $value
     * @param $object
     * @param $memberPath
     */
    public static function setNestedObjectProperty($value, &$object, $memberPath) {

        // Explode the path on . firstly
        $explodedPath = explode(".", $memberPath);

        // Pop the final entry
        $finalBit = array_pop($explodedPath);

        // Now call above function to get down to the point we need
        if (sizeof($explodedPath) > 0) {
            $immediateObject = &ObjectUtils::getNestedObjectProperty($object, implode(".", $explodedPath));
        } else {
            $immediateObject = &$object;
        };

        $explodedMemberName = explode("[", $finalBit);

        $memberName = $explodedMemberName[0];

        if (is_array($immediateObject) || ($immediateObject instanceof AssociativeArray)) {

            $immediateObject[$memberName] = $value;

        } else {

            $functionName = "set" . $memberName;

            if (method_exists($immediateObject, $functionName)) {
                $immediateObject->$functionName($value);
            } else if (property_exists($immediateObject, $memberName)) {
                $immediateObject->$memberName = $value;
            } else {
                $currentObject = null;
            }
        }


    }

    /**
     * Special case for an array scenario
     *
     * @param $value
     * @param $object
     * @param $memberPath
     */
    public static function setNestedObjectArrayProperty($value, &$object, $memberPath) {

        // Explode the path on . firstly
        $explodedPath = explode(".", $memberPath);

        // Build an expression for an exploded path
        $expression = '$object';
        foreach ($explodedPath as $pathEntry) {

            eval('$evalToPath = ' . $expression . ';');

            $explodedEntry = explode("[", $pathEntry);

            if ($evalToPath == null) {
                if (sizeof($explodedEntry) > 1) {
                    eval ($expression . ' = array();');
                } else {
                    eval ($expression . ' = new AssociativeArray();');
                }
                eval('$evalToPath = ' . $expression . ';');
            }

            if (is_array($evalToPath) || ($evalToPath instanceof AssociativeArray)) {
                $expression .= '["' . $explodedEntry[0] . '"]';
            }

            if (sizeof($explodedEntry) > 1) {
                $expression .= "[" . $explodedEntry[1];
            }
        }


        eval($expression . ' = $value;');

    }


    /**
     * Get differing members between two serialisable source objects - this will return a structured array detailing the differences.
     * If the membersToConsider array is passed only members contained within the array will be considered otherwise all members will be considered by default.
     *
     * @param $object
     * @param $object2
     */
    public static function getDifferingMembers($object1, $object2, $membersToConsider = array(), $recursive = false) {


        if (!($object1 instanceof SerialisableObject) || !($object2 instanceof SerialisableObject)) {
            throw new Exception("You cannot compare for differing members of none serialisable objects");
        }

        $members1 = $object1->__getSerialisablePropertyMap();
        $members2 = $object2->__getSerialisablePropertyMap();

        // Turn members to consider into a values array for ease of processing
        if (is_array($membersToConsider)) {
            $membersToConsider = array_values($membersToConsider);
        }


        // Go through first object, look for differences that way.
        $differingMembers = array();
        foreach ($members1 as $member => $value) {


            if (!is_object($value) && !is_array($value)) {
                if (isset($members2[$member]) && $members2[$member] != $value) {
                    if (!$membersToConsider || is_numeric(array_search($member, $membersToConsider))) {
                        $differingMembers[$member] = array("first" => $value, "second" => $members2[$member]);
                    }
                }
            }

            if (is_object($value) && is_object($members2[$member]) && $recursive) {
                $differingSubMembers = ObjectUtils::getDifferingMembers($value, $members2[$member], null, true);
                foreach ($differingSubMembers as $key => $subMember) {
                    if (!$membersToConsider || is_numeric(array_search($member . "." . $key, $membersToConsider))) {
                        $differingMembers[$member . "." . $key] = $subMember;
                    }
                }
            }

            if (is_array($value) && is_array($members2[$member]) && $recursive) {
                foreach ($value as $key => $memberEntry) {
                    if (is_object($memberEntry) && isset($members2[$member][$key]) && is_object($members2[$member][$key])) {
                        $differingSubMembers = ObjectUtils::getDifferingMembers($memberEntry, $members2[$member][$key], null, true);
                        foreach ($differingSubMembers as $subKey => $subMember) {
                            $differingMembers[$member . "[" . $key . "]" . "." . $subKey] = $subMember;
                        }
                    }
                }
            }

        }

        return $differingMembers;

    }

    /**
     * Get a formatted string encoding the differing members.
     *
     * @param $object
     * @param $object2
     * @param $formatString
     * @param $separator
     */
    public static function getDifferingMembersFormattedString($object1, $object2, $formatString, $separator, $displayMemberKeys = array(), $recursive = false) {

        // Get the differing members
        $differingMembers = ObjectUtils::getDifferingMembers($object1, $object2, array_keys($displayMemberKeys), $recursive);

        // Loop through each differing member
        $elements = array();
        foreach ($differingMembers as $member => $data) {
            $elements[] = str_replace(array("{member}", "{first}", "{second}"), array(
                isset($displayMemberKeys[$member]) ? $displayMemberKeys[$member] : $member,
                $data["first"], $data["second"]), $formatString);
        }

        return join($separator, $elements);

    }


}
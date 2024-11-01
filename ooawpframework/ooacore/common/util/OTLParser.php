<?php

include_once OOA_CORE_ROOT . "/common/util/ArrayUtils.php";

/**
 * Parser for parsing Oxil Templating Language.  The main parse method takes a string and an associative array of parameters and returns the parsed text.
 *
 * Currently this supports the following functionality.
 *
 * {myparam} will evaluate to the value of the parameter array entry keyed in by "myparam"
 * {myparam.myprop} will evaluate to the property "myprop" on the object "myparam" which is the value of the parameter array entry keyed in by "myparam"
 * NB:  In order to use the nested property function, all objects in the hierarchy must extend SerialisableObject
 *
 * <foreach {myitem} as {} >
 * .....
 * </foreach>
 *
 * This will assume that myitem is an array and will iterate over each array parameter as entry and evaluate the enclosed text, concatenation successive loops.
 *
 * <ifset {myitem} >
 * .....
 * </ifset>
 *
 * This will evaluate the enclosed text if the parameter myitem exists, else the enclosed text will be ignored.
 *
 * <if {myitem} = {myitem2} >
 * .....
 * </if>
 *
 * This will evaluate the enclosed text if the parameter myitem is equal to myitem2. Can also use >, < <=, >=, <>, != logic. Can also compare an item to a non-quoted string:
 *
 * <if {myitem} = mystring >
 * .....
 * </if>
 *
 * We can also evaluate more than one expression by using AND or OR and enclosing all expressions in []:
 *
 * <if [ {myitem} = mystring OR {myitem} = mystring2 ] >
 * .....
 * </if>
 *
 * @author matthewbull
 *
 */
class OTLParser {

    private static $instance;
    private $paramsArray;

    // Block direct construction
    private function OTLParser() {
    }

    /**
     * Get our single instance of the Parser
     *
     * @return OTLParser
     */
    public static function instance() {

        if (OTLParser::$instance == null) {
            OTLParser::$instance = new OTLParser ();
        }

        return OTLParser::$instance;

    }

    /**
     * Parse the supplied text for OTL, evaluating all constructs for the supplied params.
     *
     * return string
     */
    public function parse($text, $params) {

        //Get params.
        $this->paramsArray = $params;

        extract($params);

        // Replace if fields.
        $text = preg_replace("/<\s*ifset\s*{(.*?)}\s*>/i", "<?php if(\$this->propertyExistenceChecker(\"\${1}\", true) != false ){ ?>", $text);
        $text = preg_replace("/<\s*\/\s*ifset\s*>/i", "<?php } ?>", $text);

        // Replace if fields.
        $text = preg_replace_callback("/<\s*if\s*\[(.*?)\]\s*>/i", array($this, "substituteIfFieldsManyConditions"), $text);
        $text = preg_replace_callback("/<\s*if\s*(.*?)\s*(<>|=|>|<|>=|<=|!=)\s*(.*?)\s*>/i", array($this, "substituteIfFields"), $text);
        $text = preg_replace("/<\s*\/\s*if\s*>/i", "<?php } ?>", $text);

        // Replace foreach fields.
        $text = preg_replace_callback("/<\s*foreach\s*{(.*?)}\s*as\s*{(.*?)}\s*>/i", array($this, "substituteForeachFields"), $text);
        $text = preg_replace("/<\s*\/\s*foreach\s*>/i", "<?php }
		 ?>", $text);

        // Replace text fields.
        $text = preg_replace_callback("/{(.+?)}/", array($this, "substituteBracedFields"), $text);

        // Replace any <?xml tags as these cause problems downstream
        $text = preg_replace("/<\?xml(.*?)\?>/", "####$1####", $text);

        // Shorten any php tags
        $text = str_replace('<' . '?php', '<' . '?', $text);
        $text = '?' . '>' . trim($text) . '<' . '?';


        ob_start();
        eval($text);
        $text = ob_get_contents();
        ob_end_clean();


        // Reinclude any <?xml tags after parsing
        $text = preg_replace("/####(.*?)####/", "<?xml$1?>", $text);


        return $text;

    }


    /**
     * Substitute braced fields
     *
     * @param array $matches
     * @return string $replacement
     */
    private function substituteBracedFields($matches) {

        // Now ignore any comments within the field (i.e. remove any stuff after a # symbol)
        $explodedKey = explode("#", $matches[1]);
        $propertyKey = $explodedKey[0];


        $propertyFound = $this->propertyExistenceChecker($propertyKey);

        if ($propertyFound == true) {

            $chain = preg_split("/(\.|:)/", $propertyKey, null, PREG_SPLIT_DELIM_CAPTURE);

            $string = "\$" . $chain [0];

            // Pop off the first element
            array_shift($chain);

            for ($i = 0; $i < sizeof($chain); $i = $i + 2) {
                $delimiter = $chain [$i];
                $memberName = $chain [$i + 1];
                if ($delimiter == ".") {
                    $string .= "->__getSerialisablePropertyValue('" . $memberName . "')";
                } else if ($delimiter == ":") {
                    $string = 'ArrayUtils::arrayElementValue(' . $string . ",'" . $memberName . "')";
                }
            }

            $replacement = "<?php echo " . $string . "; ?>";

            return $replacement;

        } else {
            return $matches [0];
        }

    }

    /**
     * Substitute braced fields in logic operators
     *
     * @param array $matches
     * @return string $replacement
     */
    private function substituteFieldsInLogic($matches) {

        $propertyFound = $this->propertyExistenceChecker($matches [1]);

        if ($propertyFound == true || $propertyFound = "is array") {

            $chain = explode(".", $matches [1]);

            $strStart = "\$" . $chain [0];

            array_shift($chain);
            $strMiddle = implode("()->get", $chain);
            $strEnd = "";
            if (sizeof($chain) > 0) {
                $strStart .= "->get";
                $strEnd = "()";
            }

            return $strStart . $strMiddle . $strEnd;
        } else {
            return "\"" . $matches [0] . "\"";
        }

    }

    /**
     * Substitute if fields
     *
     * @param array $matches
     * @return string $replacement
     */
    private function substituteIfFieldsManyConditions($matches) {

        $text = $matches [1];

        $text = preg_replace_callback("/\s*(\S+?)\s*(<>|=|>|<|>=|<=|!=)\s*(.*?)(\s+)/i", array($this, "substituteFieldsForManyIfConditions"), $text);

        $text = preg_replace("/\s+OR\s+/i", " || ", $text);
        $text = preg_replace("/\s+AND\s+/i", " && ", $text);

        $ifStatement = "<?php if( " . $text . "){
		?>";

        return $ifStatement;
    }

    private function substituteFieldsForManyIfConditions($matches) {

        if ($matches [2] == "=")
            $matches [2] = "==";

        if (preg_match("/{(.*?)}/", $matches [1])) {

            $field = trim($matches [1], "{}");

            $propertyFound = $this->propertyExistenceChecker($field);

            if ($propertyFound == true) {

                $chain = explode(".", $field);

                $strStart = "\$" . $chain [0];

                array_shift($chain);
                $strMiddle = implode("()->get", $chain);
                $strEnd = "";
                if (sizeof($chain) > 0) {
                    $strStart .= "->get";
                    $strEnd = "()";
                }

                $fieldOne = $strStart . $strMiddle . $strEnd;

            } else {
                $fieldOne = "\"" . $matches [1] . "\"";
            }
        } else {
            $fieldOne = "\"" . $matches [1] . "\"";
        }

        if (preg_match("/{(.*?)}/", $matches [3])) {

            $field = trim($matches [3], "{}");

            $propertyFound = $this->propertyExistenceChecker($field);

            if ($propertyFound == true) {

                $chain = explode(".", $field);

                $strStart = "\$" . $chain [0];

                array_shift($chain);
                $strMiddle = implode("()->get", $chain);
                $strEnd = "";
                if (sizeof($chain) > 0) {
                    $strStart .= "->get";
                    $strEnd = "()";
                }

                $fieldOne = $strStart . $strMiddle . $strEnd;

            } else {
                $fieldOne = "\"" . $matches [3] . "\"";
            }
        } else {
            $fieldTwo = "\"" . $matches [3] . "\"";
        }

        $ifStatement = $fieldOne . " " . $matches [2] . " " . $fieldTwo . " ";

        return $ifStatement;
    }

    /**
     * Substitute if fields
     *
     * @param array $matches
     * @return string $replacement
     */
    private function substituteIfFields($matches) {

        if ($matches [2] == "=")
            $matches [2] = "==";

        if (preg_match("/{(.*?)}/", $matches [1])) {

            $field = trim($matches [1], "{}");

            $propertyFound = $this->propertyExistenceChecker($field);

            if ($propertyFound == true) {

                $chain = explode(".", $field);

                $strStart = "\$" . $chain [0];

                array_shift($chain);
                $strMiddle = implode("()->get", $chain);
                $strEnd = "";
                if (sizeof($chain) > 0) {
                    $strStart .= "->get";
                    $strEnd = "()";
                }

                $fieldOne = $strStart . $strMiddle . $strEnd;

            } else {
                $fieldOne = "\"" . $matches [1] . "\"";
            }
        } else {
            $fieldOne = "\"" . $matches [1] . "\"";
        }

        if (preg_match("/{(.*?)}/", $matches [3])) {

            $field = trim($matches [3], "{}");

            $propertyFound = $this->propertyExistenceChecker($field);

            if ($propertyFound == true) {

                $chain = explode(".", $field);

                $strStart = "\$" . $chain [0];

                array_shift($chain);
                $strMiddle = implode("()->get", $chain);
                $strEnd = "";
                if (sizeof($chain) > 0) {
                    $strStart .= "->get";
                    $strEnd = "()";
                }

                $fieldOne = $strStart . $strMiddle . $strEnd;

            } else {
                $fieldOne = "\"" . $matches [3] . "\"";
            }
        } else {
            $fieldTwo = "\"" . $matches [3] . "\"";
        }

        $ifStatement = "<?php if( " . $fieldOne . " " . $matches [2] . " " . $fieldTwo . "){
		?>";

        return $ifStatement;
    }

    /**
     * Substitute if fields
     *
     * @param array $matches
     * @return string $replacement
     */
    private function substituteForeachFields($matches) {

        $this->paramsArray [$matches [2]] = "dummy";

        $propertyFound = $this->propertyExistenceChecker($matches [1]);

        if ($propertyFound == true || $propertyFound == "is array") {

            $chain = explode(".", $matches [1]);

            $strStart = "\$" . $chain [0];

            array_shift($chain);
            $strMiddle = implode("()->get", $chain);
            $strEnd = "";
            if (sizeof($chain) > 0) {
                $strStart .= "->get";
                $strEnd = "()";
            }

            $string = $strStart . $strMiddle . $strEnd;

            $foreachStatement = "<?php foreach( " . $string . " as \$" . "$matches[2]" . "){
		?>";

            return $foreachStatement;
        } else {
            return "<?php if( 1==2 ){
		?>";
        }
    }

    private function propertyExistenceChecker($property, $notBlank = false) {
        try {

            if ($this->paramsArray) {
                $chain = preg_split("/\.|:/", $property);
                $chainLength = sizeof($chain);

                if (!array_key_exists($chain [0], $this->paramsArray))
                    return false;

                $param = $this->paramsArray [$chain [0]];

                for ($i = 1; $param && $i < $chainLength; $i++) {
                    if (is_array($param)) {
                        $param = isset ($param [$chain [$i]]) ? $param [$chain [$i]] : null;
                    } else if ($param instanceof SerialisableObject) {
                        $param = $param->__getSerialisablePropertyValue($chain [$i]);
                    } else {
                        break;
                    }
                }

                // Return accordingly
                $propertyExists = ($i == $chainLength ? (is_array($param) ? "is array" : (is_object($param) ? false : true)) : false);

                return $notBlank ? ($param && $propertyExists) : $propertyExists;

            } else {
                return false;
            }
        } catch (PropertyNotReadableException $e) {
            return false;
        }
    }
}

?>
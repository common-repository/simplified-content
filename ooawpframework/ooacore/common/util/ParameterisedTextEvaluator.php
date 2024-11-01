<?php

/**
 * Helper class which takes a string with php inline code or variable names prefixed with ## such as
 * MVC substitutions or indeed email templates etc.  It converts and evaluates the string substituting the variables
 * in the passed array.
 */
class ParameterisedTextEvaluator {

    private static $instance;
    private $isMac;
    private $currentParams = null;

    // Decrease the scope of this expander to private
    // as this is a singleton object.
    private function ParameterisedTextEvaluator() {

        if (is_numeric(strpos(php_uname(), "Darwin")) || is_numeric(strpos(php_uname(), "Windows"))) {
            $this->isMac = true;;
        }
    }

    /**
     * Return the singleton instance of this expander
     *
     * @return ParameterisedTextEvaluator
     */
    public static function instance() {
        if (ParameterisedTextEvaluator::$instance == null) {
            ParameterisedTextEvaluator::$instance = new ParameterisedTextEvaluator ();
        }

        return ParameterisedTextEvaluator::$instance;
    }

    /**
     * Evaluate the passed text using the params array for substitution.
     *
     * @param string $text
     * @param array $params
     */
    public function evaluateText($text, &$params) {

        // Extract all template parameters into scope.
        extract($params);

        // Store current params for callback use
        $this->currentParams = $params;

        // Replace # symbols with echo blocks using a perl regex
        $text = preg_replace_callback("/##([\w|\.*|_]*)(\w+)(\W|\s|$)/", array($this, "replaceParameter"), $text);

        // Replace inline <?php blocks with <? sections instead if required
        // This is not required if mac version
        if (!$this->isMac) {
            $text = str_replace('<' . '?php', '<' . '?', $text);
        }

        $text = '?' . '>' . $text;

        if (!$this->isMac) {
            $text .= '<' . '?';
        }

        // Store defined variables before we evaluate
        $preVariables = get_defined_vars() ? get_defined_vars() : array();

        
        // Now use an object buffer to get the result.
        ob_start();
        eval ($text);
        $result = ob_get_contents();
        ob_end_clean();


        // Add any newly scoped variables
        $postVariables = get_defined_vars() ? get_defined_vars() : array();
        foreach ($postVariables as $key => $value) {
            if (!isset($preVariables[$key]) || ($value != $preVariables[$key])) {
                $params[$key] = $value;
            }
        }


        return $result;
    }

    // Repace out a parameter
    private function replaceParameter($matches) {

        $returnString = '<?php if (isset($params["' . $matches [1] . $matches [2] . '"])) echo $params["' . $matches [1] . $matches [2] . '"]; else echo "<MISSING PARAMETER: ' . $matches [1] . $matches [2] . '>"; ?>';
        $returnString .= "\n" . $matches [3];

        return $returnString;
    }

}

?>
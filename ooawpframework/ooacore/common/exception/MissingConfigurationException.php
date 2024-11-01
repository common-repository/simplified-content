<?php

class MissingConfigurationException extends Exception {

    public function MissingConfigurationException($configParameter) {
        parent::__construct("The configuration parameter " . $configParameter . " is required but has not been supplied.");
    }

} 
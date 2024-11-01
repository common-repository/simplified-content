<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nathan
 * Date: 18/09/13
 * Time: 12:12
 * To change this template use File | Settings | File Templates.
 */

class OOAViewNotFoundException extends Exception {

    public function OOAViewNotFoundException($viewName) {
        parent::__construct("The supplied view '" . $viewName . "' Cannot be found");
    }

}
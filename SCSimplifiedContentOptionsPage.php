<?php

/**
 * Legacy Browser Options Page, encodes the data we need to store for
 * legacy browsers.
 *
 * Class SCSimplifiedContentOptionsPage
 */
class SCSimplifiedContentOptionsPage extends OOAWPPluginOptionsPage {


    /**
     * Construct and return a valid option page descriptor for this option page.
     *
     * @return OOAWPPluginOptionsPageDescriptor
     */
    public function getOptionsPageDescriptor() {
        return new OOAWPPluginOptionsPageDescriptor('lb_options', 'Browser Settings',
            'Simplified Content',
            'manage_options',
            'lb-admin');
    }

    /**
     * Create a model and view object for the option page itself.
     *
     * @return OOAModelAndView
     */
    public function createOptionsPageModelAndView($optionsArray) {
        if (!isset($optionsArray["header-html"]) || $optionsArray["header-html"] == "")
            $optionsArray["header-html"] = file_get_contents(LB_ROOT . "/views/default-header.php");

        if (!isset($optionsArray["footer-html"]) || $optionsArray["footer-html"] == "")
            $optionsArray["footer-html"] = file_get_contents(LB_ROOT . "/views/default-footer.php");


        $model = array();
        $model["options"] = $optionsArray;
        $model["browsers"] = SCSimplifiedContentPlugin::$browsers;

        return new OOAModelAndView(__DIR__ . "/views/settings.php", $model);
    }


}
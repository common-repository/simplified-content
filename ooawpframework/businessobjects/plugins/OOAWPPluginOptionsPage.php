<?php

include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/common/OOAModelAndView.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPPluginOptionsPageDescriptor.php";

/**
 * Plugin option page object - This handles the creation of admin pages using an MVC model
 */
abstract class OOAWPPluginOptionsPage {


    /**
     * Construct and return a valid option page descriptor for this option page.
     *
     * @return OOAWPPluginOptionsPageDescriptor
     */
    public abstract function getOptionsPageDescriptor();


    /**
     * Create a model and view object for the option page itself.
     *
     * @return OOAModelAndView
     */
    public abstract function createOptionsPageModelAndView($optionsArray);


    /**
     * Sanitise the options array - Overridable.
     */
    public function sanitise($optionsArray) {
        return $optionsArray;
    }


    public function addPageToWordpress() {

        $descriptor = $this->getOptionsPageDescriptor();

        register_setting(
            $descriptor->getOptionsNamespace(), // Option group
            $descriptor->getOptionsNamespace(), // Option name
            array($this, 'sanitise') // Sanitise
        );


    }


    /**
     * Internal function called from the Plugin class to add this page to wordpress.
     */
    public function addMenuToWordpress() {

        $descriptor = $this->getOptionsPageDescriptor();


        $parentSlug = "";
        if ($descriptor->getTopLevelMenu()) {
            $function = "add_menu_page";
        } else {
            $function = "add_submenu_page";
            $parentSlug = '"' . ($descriptor->getParentMenuSlug() ? $descriptor->getParentMenuSlug() : "options-general.php") . '",';
        }

        eval($function . '(' . $parentSlug . '$descriptor->getPageTitle(),
            $descriptor->getMenuTitle(),
            $descriptor->getCapability(),
            $descriptor->getMenuSlug(),
            array( $this, "getEvaluatedOptionsPage" ),
            $descriptor->getIconURL()
        );');
    }


    /**
     * Add any page admin scripts if we are on the page, use the hook suffix to decide whether these apply to us.
     *
     * @param $hook_suffix
     */
    public function enqueueAdminScripts($hookSuffix = null) {

        // Explode the hook suffix
        $menuSlug = array_pop(explode("_", $hookSuffix));

        if ($menuSlug == $this->getOptionsPageDescriptor()->getMenuSlug()){
            $this->registerPageStyles();
            $this->registerPageScripts();
        }
    }


    public function getEvaluatedOptionsPage() {

        $descriptor = $this->getOptionsPageDescriptor();

        $modelAndView = $this->createOptionsPageModelAndView(get_option($descriptor->getOptionsNamespace()));
        print $modelAndView->evaluateView();
    }


    // Designed for overriding
    public function registerPageStyles() {

    }


    // Register page scripts
    public function registerPageScripts() {

    }


}


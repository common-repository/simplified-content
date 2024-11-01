<?php

class OOAWPPluginOptionsPageDescriptor {

    private $optionsNamespace;
    private $pageTitle;
    private $menuTitle;
    private $capability;
    private $menuSlug;
    private $topLevelMenu;
    private $parentMenuSlug;
    private $iconURL;

    public function OOAWPPluginOptionsPageDescriptor($optionsNamespace = null, $pageTitle = null, $menuTitle = null, $capability = null, $menuSlug = null, $topLevelMenu = false,
                                                     $parentMenuSlug = null, $iconURL = null) {

        $this->optionsNamespace = $optionsNamespace;
        $this->pageTitle = $pageTitle;
        $this->menuTitle = $menuTitle;
        $this->capability = $capability;
        $this->menuSlug = $menuSlug;
        $this->topLevelMenu = $topLevelMenu;
        $this->parentMenuSlug = $parentMenuSlug;
        $this->iconURL = $iconURL;
    }


    public function getPageTitle() {
        return $this->pageTitle;
    }


    public function getMenuTitle() {
        return $this->menuTitle;
    }

    public function getCapability() {
        return $this->capability;
    }

    public function getMenuSlug() {
        return $this->menuSlug;
    }

    /**
     * @return mixed
     */
    public function getParentMenuSlug() {
        return $this->parentMenuSlug;
    }

    /**
     * @return mixed
     */
    public function getTopLevelMenu() {
        return $this->topLevelMenu;
    }


    /**
     * Gets the value of optionsGroup.
     *
     * @return mixed
     */
    public function getOptionsNamespace() {
        return $this->optionsNamespace;
    }

    /**
     * @return null
     */
    public function getIconURL() {
        return $this->iconURL;
    }


}


?>
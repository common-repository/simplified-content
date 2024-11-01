<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nathan
 * Date: 18/09/13
 * Time: 10:07
 * To change this template use File | Settings | File Templates.
 */

class OOAWPPluginDescriptor {

    private $name;
    private $wpPath;
    private $installURL;
    private $pluginLinks;
    private $dependentPluginDescriptors;
    private $optionPages;
    private $widgetClasses;
    private $contentInjections;


    /**
     * Construct a new descriptor.
     *
     * @param null $name
     * @param null $wpPath
     * @param null $installURL
     */
    public function OOAWPPluginDescriptor($name = null, $wpPath = null, $installURL = null, $pluginLinks = array(), $dependentPluginDescriptors = array(),
            $optionPages = array(), $widgetClasses = array(), $contentInjections = array()) {
        $this->name = $name;
        $this->wpPath = $wpPath;
        $this->installURL = $installURL;
        $this->pluginLinks = $pluginLinks;
        $this->dependentPluginDescriptors = $dependentPluginDescriptors;
        $this->optionPages = $optionPages;
        $this->widgetClasses = $widgetClasses;
        $this->contentInjections = $contentInjections;
    }

    /**
     * @param mixed $installURL
     */
    public function setInstallURL($installURL) {
        $this->installURL = $installURL;
    }

    /**
     * @return mixed
     */
    public function getInstallURL() {
        return $this->installURL;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $wpPath
     */
    public function setWpPath($wpPath) {
        $this->wpPath = $wpPath;
    }

    /**
     * @return mixed
     */
    public function getWpPath() {
        return $this->wpPath;
    }

    /**
     * Gets the value of pluginLinks.
     *
     * @return mixed
     */
    public function getPluginLinks()
    {
        return $this->pluginLinks;
    }

    /**
     * Sets the value of pluginLinks.
     *
     * @param mixed $pluginLinks the plugin links
     *
     * @return self
     */
    public function setPluginLinks($pluginLinks)
    {
        $this->pluginLinks = $pluginLinks;

        return $this;
    }


    /**
     * As OXIL plugins are always in a folder we can assume the identifier is the prefix to the path
     */
    public function getIdentifier() {
        $a = explode("/", $this->wpPath);
        $buildPath = array_shift($a);
        return $buildPath;
    }

    /**
     * @param array $contentInjections
     */
    public function setContentInjections($contentInjections) {
        $this->contentInjections = $contentInjections;
    }

    /**
     * @return array
     */
    public function getContentInjections() {
        return $this->contentInjections;
    }

    /**
     * @param array $dependentPluginDescriptors
     */
    public function setDependentPluginDescriptors($dependentPluginDescriptors) {
        $this->dependentPluginDescriptors = $dependentPluginDescriptors;
    }

    /**
     * @return array
     */
    public function getDependentPluginDescriptors() {
        return $this->dependentPluginDescriptors;
    }

    /**
     * @param array $optionPages
     */
    public function setOptionPages($optionPages) {
        $this->optionPages = $optionPages;
    }

    /**
     * @return array
     */
    public function getOptionPages() {
        return $this->optionPages;
    }

    /**
     * @param array $widgetClasses
     */
    public function setWidgetClasses($widgetClasses) {
        $this->widgetClasses = $widgetClasses;
    }

    /**
     * @return array
     */
    public function getWidgetClasses() {
        return $this->widgetClasses;
    }



   
}


?>
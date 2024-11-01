<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nathan
 * Date: 19/09/13
 * Time: 12:07
 * To change this template use File | Settings | File Templates.
 */

class OOAWPWidgetDescriptor {

    private $id;
    private $name;
    private $description;
    private $cssClass;
    private $pluginIdentifier;
    private $shortCode;

    function __construct($id, $name, $description, $cssClass, $pluginIdentifier, $shortCode = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->cssClass = $cssClass;
        $this->pluginIdentifier = $pluginIdentifier;
        $this->shortCode = $shortCode;
    }


    /**
     * @return mixed
     */
    public function getCssClass() {
        return $this->cssClass;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPluginIdentifier() {
        return $this->pluginIdentifier;
    }

    /**
     * @return mixed
     */
    public function getShortCode() {
        return $this->shortCode;
    }


}
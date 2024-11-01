<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nathan
 * Date: 18/09/13
 * Time: 09:38
 * To change this template use File | Settings | File Templates.
 */
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/common/OOAModelAndView.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPWidgetDescriptor.php";


abstract class OOAWPMVCWidget extends WP_Widget {

    /**
     * Constructor
     */
    public function OOAWPMVCWidget() {

        $descriptor = $this->getWidgetDescriptor();

        if (!$descriptor instanceof OOAWPWidgetDescriptor) {
            die ("You must supply a valid widget descriptor to a widget");
        }


        // Register activation and deactivation hooks if required;
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));


        parent::__construct(
            $descriptor->getId(),
            $descriptor->getName(),
            array(
                'classname' => $descriptor->getCssClass(),
                'description' => $descriptor->getDescription(), $this->getWidgetDescriptor()->getPluginIdentifier()
            )
        );


        // Register admin styles and scripts
        add_action('admin_print_styles', array($this, 'registerAdminStylesForPrint'));
        add_action('admin_enqueue_scripts', array($this, 'registerAdminScripts'));

        // Register site styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'registerWidgetStyles'));
        add_action('wp_enqueue_scripts', array($this, 'registerWidgetScripts'));


        // Check for any shortcodes.
        if ($descriptor->getShortCode()) {
            add_shortcode($descriptor->getShortCode(), array($this, "processShortCode"));
        }


        // Call initialise for child initialise functionality.
        $this->initialise();

    }

    /**
     * Implement the main widget drawing method.
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        if (isset($before_widget))
            echo $before_widget;

        $modelAndView = $this->createWidgetModelAndView($args, $instance);
        echo $modelAndView->evaluateView();


        if (isset($after_widget))
            echo $after_widget;

    }


    /**
     * Call form with an instance.
     *
     * @param array $instance
     * @return string|void
     */
    public function form($instance) {
        $modelAndView = $this->createFormModelAndView($instance);
        if ($modelAndView)
            echo $modelAndView->evaluateView();
    }


    /**
     * Initialise function - must be implemented
     *
     * @return mixed
     */
    public abstract function initialise();


    /**
     * Return a widget descriptor object for describing this widget.
     *
     * @return OOAWPWidgetDescriptor
     */
    public abstract function getWidgetDescriptor();

    /**
     * Sub class method for creating a model and view
     *
     * @param array $args
     * @param array $instance
     * @return OOAModelAndView
     */
    public abstract function createWidgetModelAndView($args, $instance);


    /**
     * Sub class method for creating a model and view for the form if required for this widget.
     *
     * @param $instance
     * @return mixed
     */
    public function createFormModelAndView($instance) {
        return null;
    }


    /**
     * Dummy activate function - can be implemented optionally
     */
    public function activate() {

    }


    /**
     * Dummy deactivate function - can be implemented optionally
     */
    public function deactivate() {

    }


    /**
     * Hook for registering any widget CSS styles
     */
    public function registerWidgetStyles() {

    }


    /**
     * Hook for registering any widget scripts
     */
    public function registerWidgetScripts() {

    }


    /**
     * Hook for registering admin printing styles if required.
     */
    public function registerAdminStylesForPrint() {

    }


    /**
     * Hook for registering admin scripts if required
     */
    public function registerAdminScripts() {

    }


    /**
     * Process this widget as a short code.
     *
     * @param $attributes
     */
    public function processShortCode($attributes) {
        $modelAndView = $this->createWidgetModelAndView(array(), $attributes);
        return $modelAndView->evaluateView();
    }


}


?>
<?php

if (!defined("OOA_CORE_ROOT")) {
    define("OOA_CORE_ROOT", OOAWPFRAMEWORK_ROOT . "/ooacore");
}

// Base wp plugin functionality included
include_once ABSPATH . 'wp-admin/includes/plugin.php';

include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/ajax/OOAWPServiceDispatcher.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/util/Logger.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/util/AdminMessenger.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPPluginDescriptor.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPPluginOptionsPage.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPPluginLink.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPContentInjection.php";

include_once OOA_CORE_ROOT . "/common/businessobjects/SerialisableObject.php";

abstract class OOAWPPlugin {


    private $adminNotice;

    /**
     * Require construction with a plugin name
     *
     * @param $pluginDescriptor
     */
    public function OOAWPPlugin() {


        if ($this->resolveDependencies()) {
            $this->initialise();
            $this->activate();
        }
    }

    // Generic initialisations
    private function initialise() {

        $descriptor = $this->getPluginDescriptor();

        // load plugin text domain
        add_action('init', array($this, 'installPluginTextDomain'));

        // Register handler for plugin action links
        add_filter('plugin_action_links', array($this, 'configurePluginActionLinks'), 10, 2);


        // Register admin styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'registerAdminStyles'));
        add_action('admin_enqueue_scripts', array($this, 'registerAdminScripts'));

        // Register site styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'registerPluginStyles'));
        add_action('wp_enqueue_scripts', array($this, 'registerPluginScripts'));


        $optionPages = $descriptor->getOptionPages();
        foreach ($optionPages as $page) {
            add_action('admin_enqueue_scripts', array($page, 'enqueueAdminScripts'));
            add_action('admin_menu', array($page, 'addMenuToWordpress'));
            add_action('admin_init', array($page, 'addPageToWordpress'));
        }

        // Add content filter for content injection
        add_filter('the_content', array($this, "processContentInjections"), 15);


        // Register generic ajax handler
        $serviceDispatcher = new OOAWPServiceDispatcher();
        add_action('wp_ajax_service_dispatcher', array($serviceDispatcher, "dispatchRequest"));
        add_action('wp_ajax_nopriv_service_dispatcher', array($serviceDispatcher, "dispatchRequest"));

        // Register any widget classes
        foreach ($descriptor->getWidgetClasses() as $widgetClass) {
            $this->registerWidgetClass($widgetClass);
        }

    }


    /**
     * Get the plugin descriptor for this plugin
     */
    public abstract function getPluginDescriptor();


    /**
     * Activation logic run on successful initialisation
     *
     * @return mixed
     */
    public abstract function activate();


    /**
     * Register a widget class.
     *
     * @param $widgetClass
     */
    protected function registerWidgetClass($widgetClass) {
        add_action('widgets_init', create_function('', 'register_widget("' . $widgetClass . '");'));
    }


    private function resolveDependencies() {

        $descriptor = $this->getPluginDescriptor();


        $failedPlugins = array();
        foreach ($descriptor->getDependentPluginDescriptors() as $plugin) {
            if (!is_plugin_active($plugin->getWpPath()))
                $failedPlugins[] = '<p><strong>' . $this->getPluginDescriptor()->getName() . '</strong> requires <strong>' . $plugin->getName() . '</strong>; Click to install <a href="' . $plugin->getInstallURL() . '">' . $plugin->getName() . '</a></p>';

        }

        // If we fail, deactivate oneself and get out
        if (sizeof($failedPlugins) > 0) {

            $this->adminNotice = '<div class="updated">' . join("", $failedPlugins) . '<p> the plug-in has been <strong>deactivated</strong>.</p></div>';

            add_action('admin_init', array($this, 'deactivatePlugin'));
            add_action('admin_notices', array($this, 'displayAdminNotice'));

            return false;
        } else {
            return true;
        }

    }


    // Set the wordpress admin notice
    public function displayAdminNotice() {
        echo $this->adminNotice;
    }

    /**
     *
     */
    public function deactivatePlugin() {
        // Deactivate the current plugin.
        deactivate_plugins(plugin_basename($this->getPluginDescriptor()->getWpPath()));

        if (isset($_GET['activate']))
            unset($_GET['activate']);


    }


    public function installPluginTextDomain() {

        // Get the plugin descriptor. Title name url
        $descriptor = $this->getPluginDescriptor();

        // Get identifier slug
        $domain = $descriptor->getIdentifier();

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        // Path to language pack
        $relLangPath = $domain . '/lang';

        // Hook in a generic text domain using the plugin descriptor identifier
        load_plugin_textdomain($domain, false, $relLangPath);


    }


    /**
     * Configure our plugin action links and also disable any dependent plugin deactivates
     * at the same time.
     *
     * @param  [type] $links [description]
     * @param  [type] $file  [description]
     * @return [type]        [description]
     */
    public function configurePluginActionLinks($links, $file) {

        // Firstly, process for our plugin
        $descriptor = $this->getPluginDescriptor();


        if ($file == $descriptor->getWpPath()) {
            unset($links["edit"]);

            foreach ($descriptor->getPluginLinks() as $pluginLink) {
                $link = '<a href="' . $pluginLink->getUrl() . '">' . $pluginLink->getLabel() . '</a>';
                if ($pluginLink->getAppend() == 1) {
                    $links[] = $link;
                } else {
                    array_unshift($links, $link);
                }
            }


        }


        // Now process for any dependent plugins
        foreach ($descriptor->getDependentPluginDescriptors() as $descriptor) {
            if ($file == $descriptor->getWpPath()) {
                unset($links["deactivate"]);
            }
        }


        return $links;

    }


    // Register any content injections and process these.
    public function processContentInjections($content) {

        if (is_singular()) {


            $descriptor = $this->getPluginDescriptor();


            // Loop through each content injection and act accordingly
            foreach ($descriptor->getContentInjections() as $contentInjection) {

                if ($contentInjection instanceof OOAWPContentInjection) {


                    $postTypePositions = $contentInjection->getPostTypePositions();
                    if (is_array($postTypePositions) && sizeof($postTypePositions) > 0) {

                        $injectedModelAndView = $contentInjection->createContentModelAndView();
                        $injectionContent = $injectedModelAndView->evaluateView();


                        foreach ($postTypePositions as $position) {
                            if ($position instanceof OOAWPContentInjectionPostPosition) {


                                if (is_singular($position->getPostType())) {

                                    if ($position->getPosition() == OOAWPContentInjectionPostPosition::POSITION_ABOVE || $position->getPosition() == OOAWPContentInjectionPostPosition::POSITION_BOTH) {
                                        $content = $injectionContent . $content;
                                    }

                                    if ($position->getPosition() == OOAWPContentInjectionPostPosition::POSITION_BELOW || $position->getPosition() == OOAWPContentInjectionPostPosition::POSITION_BOTH) {
                                        $content = $content . $injectionContent;
                                    }


                                }
                            }
                        }
                    }

                }
            }

        }


        return $content;


    }


    /**
     * Hook for registering any widget CSS styles
     */
    public function registerPluginStyles() {

    }


    /**
     * Hook for registering any widget scripts
     */
    public function registerPluginScripts() {

    }


    /**
     * Hook for registering admin printing styles if required.
     */
    public function registerAdminStyles() {

    }


    /**
     * Hook for registering admin scripts if required
     */
    public function registerAdminScripts() {

    }


}

?>
<?php
/*
Plugin Name: Simplified Content
Plugin URI: http://www.oxil.uk/wordpress/wordpress-plugins/simplified-content/
Description: A Plugin which provides alternative 'simplied' content for a given list of browsers.  For example, legacy browsers such as IE8, or browsers which are not supported for other reasons, such as intranet systems.
Version: 1.0.1
Author: Oxford Information Labs
Author URI: http://www.oxil.uk
Author Email: support@oxil.co.uk
Text Domain: simplified-content
Domain Path: /lang/
Network: false
License: GPLv2 or later
License URI: http://www.oxil.uk

Copyright 2015 Oxford Information Labs Limited (support@oxil.co.uk)

All Rights Reserved.
*/

// Define our various source roots.
if (!defined("OOAWPFRAMEWORK_ROOT")) {
    define("OOAWPFRAMEWORK_ROOT", __DIR__ . "/ooawpframework");
}

if (!defined("OOA_CORE_ROOT")) {
    define("OOA_CORE_ROOT", __DIR__ . "/ooawpframework/ooacore");
}

if (!defined("LB_ROOT")) {
    define("LB_ROOT", __DIR__);
}

// Include our plugin
include_once LB_ROOT . "/SCSimplifiedContentPlugin.php";

// Construct the plugin.
new SCSimplifiedContentPlugin();

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'sc_add_plugin_page_settings_link');
function sc_add_plugin_page_settings_link( $links ) {
    $links[] = '<a href="' .
        admin_url( 'options-general.php?page=lb-admin' ) .
        '">' . __('Settings') . '</a>';
    return $links;
}

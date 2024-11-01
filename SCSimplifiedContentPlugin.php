<?php

include_once OOA_CORE_ROOT . "/common/util/URLHelper.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/plugins/OOAWPPlugin.php";
include_once LB_ROOT . "/SCSimplifiedContentOptionsPage.php";
include_once LB_ROOT . "/businessobjects/SCBrowserDetector.php";

/**
 * Main Legacy Browser Plugin wrapper class
 */
class SCSimplifiedContentPlugin extends OOAWPPlugin {


    public static $browsers = array("IE 6 and lower" => array("Internet Explorer", "6"),
        "IE7" => array("Internet Explorer", "7"),
        "IE8" => array("Internet Explorer", "8"),
        "IE9" => array("Internet Explorer", "9"),
        "Firefox" => array("Firefox"),
        "Safari" => array("Safari"),
        "Opera" => array("Opera"),
        "Google Chrome" => array("Chrome"));


    /**
     * Get the plugin descriptor for this plugin
     */
    public function getPluginDescriptor() {
        return new OOAWPPluginDescriptor("Simplified Content", "simplified-content/simplified-content.php", "http://www.oxil.co.uk", array(), array(),
            array(new SCSimplifiedContentOptionsPage()), array(), array());
    }

    /**
     * Activation logic run on successful initialisation
     *
     * @return mixed
     */
    public function activate() {

        add_action('wp_loaded', array($this, "checkForTargetBrowser"));
    }


    // Check for target browsers.  Do this as a header action as we have the correct scope for inferring posts by this stage.
    public function checkForTargetBrowser() {

        if ($this->isTargetBrowser()) {

            $options = get_option("lb_options");

            // Add the simplified header to the page.
            echo $options["header-html"];

            $postId = null;

            // if we are at the root context, show the site map.
            if ($_SERVER["REQUEST_URI"] == "/") {

                $postId = $options['sitemap-page-id'];
            } else {
                echo '<br /><a href="/">&lt; Back to site map</a><br /><br />';
                $postId = url_to_postid($_SERVER["REQUEST_URI"]);
            }


            $post = get_post($postId);

            $postContent = $post->post_content;
            $postContent = do_shortcode($postContent);

            echo "<h1>" . $post->post_title . "</h1>" . $postContent;

            // Add the simplified footer to the page.
            echo $options["footer-html"];

            exit();


        }
    }


    // Check to see whether the current browser is a legacy one or not.
    private function isTargetBrowser() {

        $currentBrowser = new SCBrowserDetector();
        $options = get_option("lb_options");

        $detectedBrowser = null;
        foreach (SCSimplifiedContentPlugin::$browsers as $browser => $triggers) {
            if ($triggers[0] == $currentBrowser->getBrowser()) {
                if (sizeof($triggers) > 1) {
                    if ($triggers[1] == floor($currentBrowser->getVersion())) {
                        $detectedBrowser = $browser;
                        break;
                    }
                } else {
                    $detectedBrowser = $browser;
                    break;
                }
            }
        }

        if ($detectedBrowser) {

            $optionName = "trigger-" . strtolower(str_replace(" ", "", $detectedBrowser));
            if (isset($options[$optionName])) {
                return true;
            }

        }

        return false;
    }

}
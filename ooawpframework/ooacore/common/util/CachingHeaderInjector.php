<?php


/**
 * Utility class for injecting cache headers.
 *
 * @author mark
 *
 */
class CachingHeaderInjector {

    private static $instance;

    // Prevent direct construction
    private function CachingHeaderInjector() {
    }

    /**
     * Get the singleton copy of this class
     *
     * @return CachingHeaderInjector
     */
    public static function instance() {
        if (!CachingHeaderInjector::$instance) {
            CachingHeaderInjector::$instance = new CachingHeaderInjector ();
        }

        return CachingHeaderInjector::$instance;
    }

    /**
     * Add the standard revalidating header configured using the
     * static.content.caching.days configuration parameter if configured.
     *
     */
    public function addStandardHeader() {

        $staticContentDays = Configuration::readParameter("static.content.caching.days");

        if ($staticContentDays)
            $this->addCustomHeader($staticContentDays);
    }

    /**
     * Add a custom caching header using the passed number of days and set the revalidate flag if passed.
     *
     * @param integer $numberOfDays
     * @param boolean $revalidate
     */
    public function addCustomHeader($numberOfDays = 365, $revalidate = true) {

        // Add cache control header if revalidate
        if ($revalidate)
            header("Cache-Control: max-age=" . ($numberOfDays * 60 * 60 * 24) . " must-revalidate");
        else
            header("Cache-Control: max-age=" . ($numberOfDays * 60 * 60 * 24));

        // Add the Expires header.
        $offset = 60 * 60 * 24 * $numberOfDays;
        $expStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        header($expStr);

        // Add the last modified header.
        $lastModified = "Last-Modified: " . gmdate("D, d M Y H:i:s", time()) + " GMT";
        header($lastModified);

        header_remove("Pragma");

    }

}

?>
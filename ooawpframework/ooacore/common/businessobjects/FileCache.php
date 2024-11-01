<?php

/**
 * File cache for storing a cache of files
 *
 * Class FileCache
 */
class FileCache {

    private $cacheBaseDir;

    /**
     * Construct with a cache base.
     *
     * @param null $cacheBaseDir
     */
    public function FileCache($cacheBaseDir = null) {
        $this->cacheBaseDir =
            $cacheBaseDir ? $cacheBaseDir : (Configuration::readParameter("filecache.basedir") ? Configuration::readParameter("filecache.basedir") : "filecache");
    }


    /**
     * Get a cached file using the cache key - either a string or an array of keys
     *
     * @param mixed $cacheKey
     */
    public function getCachedFile($cacheKey) {

        // Get directory and filename
        list($directory, $filename) = $this->getDirectoryAndFilenameFromKey($cacheKey);

        if (file_exists($directory . "/" . $filename) && !is_dir($directory . "/" . $filename)) {
            return file_get_contents($directory . "/" . $filename);
        }

        return null;


    }

    /**
     * Cache a file using the file contents - overwrite if necessary.
     *
     * @param $cacheKey
     * @param $fileContents
     */
    public function cacheFile($cacheKey, $fileContents) {

        // Get directory and filename
        list($directory, $filename) = $this->getDirectoryAndFilenameFromKey($cacheKey);

        // Make the cache directory
        if (!file_exists($directory)) mkdir($directory, 0777, true);

        // Save the file
        file_put_contents($directory . "/" . $filename, $fileContents);

    }


    /**
     * Purge cached entries.  If the cache is an array it will purge all entries matching the
     * supplied key (or sub keys)
     *
     * @param $cacheKey
     */
    public function purgeCachedFiles($cacheKey) {
        // Get directory and filename
        list($directory, $filename) = $this->getDirectoryAndFilenameFromKey($cacheKey);

        // Remove the directory
        passthru("rm -rf " . $directory . "/" . $filename);

    }


    // Get the filename and directory from a key
    private function getDirectoryAndFilenameFromKey($cacheKey) {

        if (!is_array($cacheKey)) {
            $cacheKey = array($cacheKey);
        }

        $filename = array_pop($cacheKey);
        $dir = $this->cacheBaseDir . "/" . join("/", $cacheKey);


        // Return dir and filename
        return array($dir, $filename);

    }


}
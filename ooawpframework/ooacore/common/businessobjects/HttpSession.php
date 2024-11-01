<?php

include_once OOA_CORE_ROOT . "/common/exception/BadParameterException.php";

/**
 * NB:  THIS CLASS IS REPLACING THE OLD SESSION CLASS TO WORK AROUND A CLASS CLASH WITH OTHER SOFTWARE
 *
 * Convenient static class for accessing the http session.  Adds built in methods for the core stuff like getting
 * logged in user as well as a generic get / set property for user use.
 *
 */
class HttpSession {

    private static $instance;
    private $sessionData = null;

    // Private constructor - should use instance method
    private function HttpSession() {
    }

    /**
     * Set a session value by key and invalidate the session data
     *
     * @param string $key
     * @param any $value
     */
    public function setValue($key, $value) {
        $this->startSession();
        $_SESSION [$key] = $value;
        $this->sessionData = null;
        session_write_close();
    }

    /**
     * Get a session value by key
     *
     * @param unknown_type $key
     */
    public function getValue($key) {
        $allValues = $this->getAllValues();
        if (isset($allValues[$key])) {
            return $allValues[$key];
        } else {
            return null;
        }
    }

    /**
     * Get all values - return as array and close session to prevent threading locks.
     */
    public function getAllValues() {

        if (!$this->sessionData) {
            $this->startSession();
            $this->sessionData = isset($_SESSION) ? $_SESSION : array();
            session_write_close();
        }

        return $this->sessionData;
    }


    /**
     * Clear the session of all values
     *
     */
    public function clearAll() {
        $this->startSession();
        $_SESSION = array();
        $this->sessionData = null;
        session_write_close();
    }

    /**
     * Save and close the session - does nothing
     * but is kept for legacy compatibility - superceded by logic above
     *
     */
    public function saveAndClose() {
    }


    /**
     * Force a reload of the session
     */
    public function reload() {
        $this->sessionData = null;
        $this->getAllValues();
    }


    // Start the session
    private function startSession() {

        if (session_id() == "" || session_id() == null) {
            if (isset ($_REQUEST ["PHPSESSID"])) {
                session_id($_REQUEST ["PHPSESSID"]);
            } else if (isset ($_REQUEST ["HTTPSESSID"])) {
                session_id($_REQUEST ["HTTPSESSID"]);
            }
        }

        @session_start();

    }


    /**
     * Enforce a singleton session object
     *
     * @return HttpSession
     */
    public static function instance() {


        if (HttpSession::$instance == null) {
            HttpSession::$instance = new HttpSession ();
        }

        return HttpSession::$instance;
    }

}

?>
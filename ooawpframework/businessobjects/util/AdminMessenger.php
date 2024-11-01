<?php

/**
 * Hook into the Wordpress Messages API to allow display of messages.
 *
 * Class AdminMessenger
 */
class AdminMessenger {

    private static $instance;

    // Private only constructor.
    private function AdminMessenger() {
    }


    /**
     * Add an error message
     *
     * @param $messageText
     */
    public function addErrorMessage($messageText) {
        echo '<div class="error">' . $messageText . '</div>';
    }


    /**
     * Add a standard info message to the stack
     *
     * @param $messageText
     */
    public function addInfoMessage($messageText) {
        echo '<div class="updated">' . $messageText . '</div>';
    }


    /**
     * Add a custom message to the stack
     *
     * @param $messageHTML
     */
    public function addCustomMessage($messageHTML) {
        echo $messageHTML;
    }


    /**
     * Create a singleton of this object
     *
     * @return AdminMessenger
     */
    public static function instance() {
        if (!AdminMessenger::$instance) {
            AdminMessenger::$instance = new AdminMessenger();
        }

        return AdminMessenger::$instance;
    }


}
<?php

    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 25.12.16
     * Time: 16:44
     * "SessionController" by Moritz Menzel @ 2016
     */
    class SessionController
    {
        public function __construct($pdo)
        {
            $HTTPS_ONLY = false;  /** !!!CHANGE THIS TO TRUE WHEN NOT ON LOCAL SERVER!!! */
            session_set_cookie_params(3600, "/", "localhost", $HTTPS_ONLY, true);
            session_start();
            $this->pdo = $pdo;
            if(empty($_SESSION['loggedin']) || !isset($_SESSION['loggedin'])){
                session_unset();
                $_SESSION['loggedin'] = false;
            }
        }

        //TODO: Implement status observing in AJAX calls xD
        /**
         * create function
         * accstatus 1 --> #activate-account
         * accstatus 2 --> #waiting-for-confirmation
         * accstatus 3 --> _redirectUserPage()
         *  --> UserPage by acctype
         *      acctype 1 --> Admin
         *      acctype 2 --> UserManagement
         *      acctype 3 --> Teacher
         */

        public function logout()
        {
            // Delete Session, not just its content
            // Idea from php.net/manual/de/function.session_destroy.php
            $_SESSION = array();
            if(ini_get('session.use_cookies')) {
                $cp = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $cp['path'], $cp['domain'], $cp['secure'], $cp['httponly']);
            }
            session_destroy();

            // Instant Restart of session, otherwise some parts of the system won't work properly
            $this->__construct($this->pdo);
            return true;
        }
    }
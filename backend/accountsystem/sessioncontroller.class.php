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
            session_set_cookie_params(1800, "/raumreservierung/project", "", $HTTPS_ONLY, true);
            session_start();
            session_regenerate_id(true);
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
            unset($_SESSION['loggedin']);
            session_destroy();
            return true;
        }

        public function getAccType(){
            return $_SESSION['acctype'];
        }

    }
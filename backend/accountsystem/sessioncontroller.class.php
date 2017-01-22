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
            $HTTPS_ONLY = false;  /** TODO: !!!CHANGE THIS TO TRUE WHEN NOT ON LOCAL SERVER!!! */
            session_set_cookie_params(1800, "/raumreservierung/project", "", $HTTPS_ONLY, true);
            session_start();
            session_regenerate_id(true);
            $this->pdo = $pdo;
            if(empty($_SESSION['loggedin']) || !isset($_SESSION['loggedin'])){
                session_unset();
                $_SESSION['loggedin'] = false;
            }
        }

        public function initialize()
        {
            // :: START LoginSystem Start Routine ::

            if(empty($_SESSION['loggedin']) || !isset($_SESSION['loggedin'])){
                session_unset();
                $_SESSION['loggedin'] = false;
            } elseif($_SESSION['loggedin'] === true && isset($_SESSION['acctype']) && isset($_SESSION['accstatus'])) {
                if($_SESSION['accstatus'] == 1){

                    if(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) !== "/raumreservierung/project/main/activation")
                    {
                        header('Location: /raumreservierung/project/main/activation');
                    }


                } elseif($_SESSION['accstatus'] == 2) {

                    if(isset($_SESSION['last_status_change'])){

                        $change = strtotime($_SESSION['last_status_change']);
                        $expire = $change + (3600*11);
                        $now = time();

                        if($expire > $now){
                            //Still active
                            if(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) !== "/raumreservierung/project/main/notconfirmed"){
                                header('Location: /raumreservierung/project/main/notconfirmed');
                            }
                        } else {
                            //Deactivation routine
                            require_once('userManagement.class.php');
                            $um = new userManagement($this->pdo);
                            if($um !== false) {
                                $um->deactivateUser($_SESSION['name']);
                            }
                            if(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) !== "/raumreservierung/project/main/notconfirmed"){
                                header('Location: /raumreservierung/project/main/notconfirmed');
                            }
                        }

                    } else {
                        $this->logout();
                        if(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] !== "/raumreservierung/project")){
                            header('Location: /raumreservierung/project');
                        }
                    }

                } elseif($_SESSION['accstatus'] == 3){
                    $file = str_replace('/index.php','', $_SERVER['SCRIPT_NAME']);
                    switch($_SESSION['acctype']) {
                        case "1":
                            //Global Admin
                            $url = "/raumreservierung/project/main/admin";
                            if($file != $url && $file != $url."#raumverwaltung" && $file != $url."#nutzerverwaltung")
                            {
                                header('Location: '.$url);
                            }
                            break;
                        case "2":
                            //User Management
                            $url = "/raumreservierung/project/main/management";
                            if($file != $url)
                            {
                                header('Location: '.$url);
                            }
                            break;
                        case "3":
                            //Teacher
                            $url = "/raumreservierung/project/main/teacher";
                            if($file != $url)
                            {
                                header('Location: '.$url);
                            }
                            break;
                    }
                }
            } elseif($_SESSION['loggedin'] !== true && $_SERVER['SCRIPT_NAME'] != "/raumreservierung/project/index.php") {
                header('Location: /raumreservierung/project/');
            }

            // :: END LoginSystem Start Routine ::
        }

        public function logout()
        {
            unset($_SESSION['loggedin']);
            session_destroy();
        }

        public function getAccType(){
            return $_SESSION['acctype'];
        }

    }
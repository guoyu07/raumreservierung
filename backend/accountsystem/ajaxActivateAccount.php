<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 25.12.16
     * Time: 23:05
     */

    function quit()
    {
        echo json_encode(array("success" => false, "message" => "Es ist ein Fehler bei der Anmeldung aufgetreten. Sollte dieser Fehler &ouml;fter auftreten, melden Sie sich bitte bei einem Administrator."));
        require_once('sessioncontroller.class.php');
        require_once('../db/conf/dbconf.php');
        $sess = new SessionController($pdo);
        $sess->logout();
    }

    if(isset($_POST['name']) && isset($_POST['pw']) && isset($_POST['email']))
    {

        require_once('../db/conf/dbconf.php');

        $name   = htmlentities($_POST['name'], ENT_QUOTES);
        $pw     = htmlentities($_POST['pw'], ENT_QUOTES);
        $email  = htmlentities($_POST['email'], ENT_QUOTES);

        if(strlen($pw) >= 8 && strlen($pw) <= 32 && strlen($email) >= 5 && strlen($email) <= 64 && $pw != "gykl@2016"){

	    require_once('sessioncontroller.class.php');
            $sess = new SessionController($pdo);
            $sess->logout();

            require_once('userManagement.class.php');
            $um = new userManagement($pdo);
            $result = $um->activateUser($name, $pw, $email);

            if($result['error'] === false) {
                echo json_encode(array("success" => true));
            } else {
                echo json_encode(array("success" => false, "message" => $result['message']));
            }

        } else {
            quit();
        }

    } else {
        echo json_encode(array("success" => false, "message" => "Fehler: Es konnten nicht alle Daten &uuml;bermittelt werden!"));
    }

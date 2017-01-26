<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 25.01.17
     * Time: 22:30
     */

    if(isset($_POST['name']) && isset($_POST['code'])) {

        // Safe Decode Name & Code
        $name = preg_replace('/[^A-Za-z0-9öüäÖÜÄß]/', '', $_POST['name']);
        $code = preg_replace('/[^a-f0-9]/', '', $_POST['code']);

        if(strlen($name) < 4) {
            echo json_encode(array("success" => false, "message" => "Der übermittelte Name ist ungültig!"));
        } else {
            if(strlen($code) < 64) {         // Generated Activationcode will be 64 bytes long
                echo json_encode(array("success" => false, "message" => "Der Aktivierungscode ist ungültig!"));
            } else {
                require_once ('../accountsystem/userManagement.class.php');
                require_once ('../db/conf/dbconf.php');
                $um = new userManagement($pdo);
                $res = $um->confirmUser($name, $code);
                if(!empty($res)) {
                    if($res['error'] == false) {
                        echo json_encode(array("success" => true, "message" => $res['message']));
                    } else {
                        echo json_encode(array("success" => false, "message" => $res['message']));
                    }
                } else {
                    echo json_encode(array("success" => false, "message" => "Es ist ein Fehler bei der Verbindung mit der Datenbank aufgetreten!"));
                }
            }
        }

    } else {
        // No Data submitted!
        echo json_encode(array("success" => false, "message" => "Es wurden nicht alle benötigten Daten übermittelt!"));
    }
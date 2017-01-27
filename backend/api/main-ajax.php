<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 20.01.17
     * Time: 21:40
     */

    require_once ('../accountsystem/sessioncontroller.class.php');
    require_once ('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(isset($_POST['r'])) {
        switch($_POST['r']) {

            case "getSessionData":

                if(isset($_SESSION)) {
                    $s = $_SESSION;                 //Shortening things up
                    if($s['loggedin'] === true && isset($s['name']) && isset($s['acctype']) && isset($s['accstatus'])) {
                        echo json_encode(array(
                            "success" => true,
                            "data" => array(
                                "loggedin" => $s['loggedin'],
                                "name" => $s['name'],
                                "type" => $s['acctype'],
                                "status" => $s['accstatus']
                            )
                        ));
                    } else {
                        echo json_encode(array(
                            "success" => false,
                            "data" => array(
                                "loggedin" => $s['loggedin']
                            )
                        ));
                    }
                } else {
                    // Cookies disabled or SessionController failed
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Es konnte keine Sitzung gestartet werden. Bitte überprüfen Sie Ihre Cookie-Einstellungen in Ihrem Browser!"
                    ));
                }
                break;
            case "logout":
                if($sess->logout()) {
                    echo json_encode(array("success" => true));
                } else {
                    echo json_encode(array("success" => false, "message" => "Es ist ein interner Skriptfehler aufgetreten!"));
                }
                break;
            default:
                echo json_encode(array("success" => false, "message" => "Die geforderte Abfrage konnte nicht gefunden werden!"));

        }
    } else {
        echo json_encode(array("success" => false, "message" => "Es wurde kein Abfrage-Typ angegeben!"));
    }
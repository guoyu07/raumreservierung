<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 25.12.16
     * Time: 23:05
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(isset($_POST['name']) && isset($_POST['pw']) && isset($_POST['email'])) {

        if(isset($_SESSION) && $_SESSION['loggedin'] == true && $_SESSION['accstatus'] == 1) {
            $name   = htmlentities($_POST['name'], ENT_QUOTES);
            $pw     = htmlentities($_POST['pw'], ENT_QUOTES);
            $email  = htmlentities($_POST['email'], ENT_QUOTES);

            /** @var $pw | @var $email --> Encoding JavaScript Patterns */

            $pw = preg_replace("/::AMP::/", "&", $pw);
            $pw = preg_replace("/::QUOT::/", "\"", $pw);

            $email = preg_replace("/::AMP::/", "&", $email);
            $email = preg_replace("/::QUOT::/", "\"", $email);

            if(strlen($pw) >= 8 && strlen($pw) <= 64 && strlen($email) >= 5 && strlen($email) <= 64 && $pw != "gykl@2016" && $pw != "passwort" && $pw != "password" && $pw != "12345678"){

                require_once ('../accountsystem/userManagement.class.php');
                $um = new userManagement($pdo);
                $result = $um->activateUser($name, $pw, $email);

                if($result['error'] === false) {
                    $sess->logout();
                    echo json_encode(array("success" => true, "request" => "activateAccount"));
                } else {
                    echo json_encode(array("success" => false, "message" => $result['message']));
                }

            } else {
                echo json_encode(array("success" => false, "message" => "Die eingegebenen Daten sind ungültig. Bitte überprüfen Sie Ihre Eingaben und wählen Sie ein sicheres Passwort!"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Sie müssen angemeldet sein, um diese Aktion ausführen zu können!"));
        }

    } elseif(isset($_POST['request']))  {
        if($_POST['request'] == "getUserData") {

            if(isset($_SESSION['name']) && isset($_SESSION['acctype']) && isset($_SESSION['accstatus']) && isset($_SESSION['loggedin'])) {

                if($_SESSION['accstatus'] != 2) {
                    $name = $_SESSION['name'];
                    $type = "";
                    switch($_SESSION['acctype']){
                        case 1:
                            $type = "Administratorkonto";
                            break;
                        case 2:
                            $type = "Nutzerverwaltungskonto";
                            break;
                        case 3:
                            $type = "Lehrerkonto";
                            break;
                    }
                    $status = $_SESSION['accstatus'];
                    $loggedin = $_SESSION['loggedin'];

                    echo json_encode(array("success" => true, "name" => $name, "type" => $type, "status" => $status, "loggedin" => strval($loggedin), "request" => "getUserData"));
                } else {
                    json_encode(array("success" => false, "invalid" => true));
                }
            } else {
                echo json_encode(array("success" => false, "invalid" => true));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Die geforderte Abfrage existiert nicht!"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Fehler: Es konnten nicht alle Daten &uuml;bermittelt werden!"));
    }
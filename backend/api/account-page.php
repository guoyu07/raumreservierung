<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 29.01.17
     * Time: 18:42
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(isset($_SESSION)) {

        if(isset($_POST['request'])) {
            switch($_POST['request']) {
                case "getUserData":
                    if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {

                        require_once ('../accountsystem/userManagement.class.php');
                        $um = new userManagement($pdo);
                        $name = $_SESSION['name'];
                        $email = ($_SESSION['email'] == null) ? "Nicht angegeben" : $_SESSION['email'];

                        $nd = $um->getFullName($name);
                        $prename = is_array($nd) ? $nd['prename'] : "Nicht angegeben";
                        $surname = is_array($nd) ? $nd['surname'] : "Nicht angegeben";
                        $fullname = is_array($nd) ? $prename." ".$surname : $name;
                        $kuerzel = is_array($nd) ? $nd['lehrer_kurz'] : "Nicht angegeben";

                        $status = "-";
                        switch($_SESSION['accstatus']) {
                            case 1:
                                $status = "Deaktiviert";
                                break;
                            case 2:
                                $status = "Bestätigung ausstehend";
                                break;
                            case 3:
                                $status = "Aktiviert";
                                break;
                        }

                        $type = "-";
                        switch($_SESSION['acctype']) {
                            case 1:
                                $type = "Administrator";
                                break;
                            case 2:
                                $type = "Nutzerverwaltung";
                                break;
                            case 3:
                                $type = "Lehrer";
                                break;
                        }

                        echo json_encode(array(
                            "success" => true,
                            "loggedin" => true,
                            "name" => $name,
                            "email" => $email,
                            "prename" => $prename,
                            "surname" => $surname,
                            "fullname" => $fullname,
                            "lehrer_kurz" => $kuerzel,
                            "status" => $status,
                            "type" => $type
                        ));

                    } else {
                        echo json_encode(array("success" => true, "loggedin" => false));
                    }
                    break;

                case "changePassword":
                    if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['name']) && isset($_SESSION['accstatus'])) {

                        if($_SESSION['accstatus'] == 3) {
                            // Allow password changin only if account is activated :)
                            if(isset($_POST['oldpw']) && isset($_POST['oldpw'])) {

                                $name = $_SESSION['name'];
                                $old = preg_replace("/::AMP::/", "&", $_POST['oldpw']);
                                $new = preg_replace("/::AMP::/", "&", $_POST['newpw']);

                                require_once ('../accountsystem/userManagement.class.php');

                                $um = new userManagement($pdo);
                                $status = $um->selfChangePassword($name, $old, $new);

                                if($status['error'] == true) {
                                    echo json_encode(array("success" => false, "message" => $status['message']));
                                } else {
                                    echo json_encode(array("success" => true));
                                }

                            } else {
                                echo json_encode(array("success" => false, "message" => "Es wurden nicht alle benötigten Daten angegeben!"));
                            }
                        } else {
                            echo json_encode(array("success" => false, "message" => "Sie können Ihr Passwort erst ändern, wenn Sie Ihren Account aktiviert haben."));
                        }

                    } else {
                        echo json_encode(array("success" => false, "message" => "Es ist ein Fehler beim auslesen der Sitzungsvariablen aufgetreten!"));
                    }
                    break;

                default:
                    echo json_encode(array("success" => false, "message" => "Die geforderte Abfrage konnte nicht gefunden werden!"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Es wurde keine auszuführende Abfrage angegeben!"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Es konnte keine Sitzung gestartet werden. Überprüfen sie bitte Ihre Cookie-Einstellungen des Browsers!"));
    }
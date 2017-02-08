<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 07.02.17
     * Time: 17:02
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(!isset($_SESSION) || !isset($_SESSION['loggedin']) || !isset($_SESSION['accstatus']) || !isset($_SESSION['acctype']) || !isset($_SESSION['name']) || $_SESSION['loggedin'] !== true) {
        echo json_encode(array("success" => false, "message" => "Sie müssen eingeloggt sein, um Zugriff auf diese Seite zu erhalten!", "sessionError" => true));
    } else {
        if($_SESSION['accstatus'] == 3 && $_SESSION['acctype'] == 3) {

            if(isset($_POST['request'])) {
                switch ($_POST['request']) {
                    case "getUserData":
                        // Session & User Data in Session is ok
                        // Return fullname
                        require_once('../accountsystem/userManagement.class.php');
                        $um = new userManagement($pdo);
                        $nameData = $um->getFullName($_SESSION['name']);
                        $lehrer_kurz = "";
                        $fullname = "";

                        if(is_array($nameData) && !empty($nameData)) {
                            $lehrer_kurz = $nameData['lehrer_kurz'];
                            $fullname = $nameData['prename']." ".$nameData['surname'];
                        } else {
                            $fullname = $_SESSION['name'];
                        }
                        // Final Data Return
                        echo json_encode(array("success" => true, "fullname" => $fullname, "lehrer_kurz" => $lehrer_kurz, "name" => $_SESSION['name']));
                    break;
                    case "getRoomlist":
                        require_once ('../roomsystem/reservation.class.php');
                        $reservation = new reservation($pdo);

                        $data = $reservation->getReservableRooms();
                        if($data != false && !empty($data)) {
                            echo json_encode(array("success" => true, "data" => $data));
                        } else {
                            echo json_encode(array("success" => false, "message" => "Der Server lieferte ein leeres Resultat!"));
                        }
                        break;
                    default:
                        echo json_encode(array("success" => false, "message" => "Die angeforderte Anfrage konnte nicht gefunden werden!"));
                        break;

                        /** VALIDATE DATE
                         *
                        $timestamp = $_POST['date'];
                        $date = new DateTime();
                        $date = date_time_set($date, 0, 0, 0);
                        if($timestamp >= $date->getTimestamp() && $timestamp <= time()+3600*24*14) { }
                         *
                         */
                }
            } else {
                echo json_encode(array("success" => false, "message" => "Die angeforderte Abfrage konnte nicht gefunden werden!"));
            }

        } else {
            echo json_encode(array("success" => false, "message" => "Sie müssen ein aktiviertes Lehrerkonto besitzen, um Zugriff auf diese Seite zu erhalten!", "sessionError" => true));
        }
    }
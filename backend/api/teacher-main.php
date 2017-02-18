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
                    case "getTimeList":
                        if(isset($_POST['date']) && isset($_POST['raumid'])) {
                            $time = $_POST['date'];
                            $raumid = $_POST['raumid'];

                            if(!empty($time) && !empty($raumid)) {

                                // Get Weekday (1=Monday; 5=Friday)
                                $weekday = date('w', $time);
                                $date = date('Y-m-d', $time);

                                require_once('../roomsystem/reservation.class.php');
                                $res = new reservation($pdo);
                                $list = $res->getTimeList($_SESSION['name'], $weekday, $raumid, $date);

                                if($list['error'] == false) {

                                    $a = $list['list'];

                                    // Stunden-Array

                                    $stunden = array("1","2","3","4","5","6","7","8","9");
                                    $resStunden = array();
                                    foreach($a as $l) {
                                        array_push($resStunden, $l['stunde']);
                                    }

                                    $d = array_diff($stunden, $resStunden);

                                    foreach($d as $s) {
                                        array_push($a, array(
                                            "stunde" => $s,
                                            "besetzt" => false
                                        ));
                                    }

                                    for($i=0; $i < count($a); $i++) {
                                        $a[$i]['besetzt'] = isset($a[$i]['lehrer_kurz']) ? "Ja" : "Nein";
                                        $a[$i]['von'] = isset($a[$i]['lehrer_kurz']) ? $a[$i]['lehrer_kurz'] : "";
                                        $a[$i]['woche'] = isset($a[$i]['woche']) ? $a[$i]['woche'] : "";
                                     }

                                     sort($a);

                                    echo json_encode(array("success" => true, "list" => $a));

                                } else {
                                    echo json_encode(array("success" => false, "message" => $list['message']));
                                }

                            } else {
                                echo json_encode(array("success" => false, "message" => "Die übermittelten Daten sind ungültig!"));
                            }

                        } else {
                            echo json_encode(array("success" => false, "message" => "Es wurden nicht alle Daten angegeben!"));
                        }
                        break;
                    case "reserveRoom":
                        if(isset($_POST['date']) && isset($_POST['room']) && isset($_POST['stunde'])) {

                            $time = $_POST['date'];
                            $raumid = $_POST['room'];
                            $stunde = $_POST['stunde'];
                            $weekday = date('w', $time);
                            $date = date('Y-m-d', $time);

                            require_once ('../roomsystem/reservation.class.php');
                            $reservation = new reservation($pdo);
                            $state = $reservation->reserveRoom($_SESSION['name'], $raumid, $stunde, $weekday, $date);
                            if($state['error'] == false) {
                                echo json_encode(array("success" => true));
                            } else {
                                echo json_encode(array("success" => false, "message" => $state['message']));
                            }

                        } else {
                            echo json_encode(array("success" => false, "message" => "Die angegebenen Daten sind ungültig!"));
                        }
                        break;
                    case "getReservations":
                        // TODO: Get Reservations :D
                        /**
                         * SELECT * FROM reservations
                         * WHERE reservations.name = ::$_SESSION['name']::
                         * ORDER BY reservations.datum DESC
                         */
                        // Temporary Message until not finished
                        echo json_encode(array("success" => false, "message" => "Request works fine, but corresponding function is not yet finished :c (teacher-main.php:130)"));
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
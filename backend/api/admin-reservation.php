<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 18.02.17
     * Time: 20:57
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(!isset($_SESSION) || !isset($_SESSION['loggedin']) || !isset($_SESSION['accstatus']) || !isset($_SESSION['acctype']) || !isset($_SESSION['name']) || $_SESSION['loggedin'] !== true) {
        echo json_encode(array("success" => false, "message" => "Sie müssen eingeloggt sein, um Zugriff auf diese Seite zu erhalten!", "sessionError" => true));
    } else {
        if($_SESSION['accstatus'] == 3 && $_SESSION['acctype'] == 1) {

            if(isset($_POST['request'])) {
                switch ($_POST['request']) {
                    case "getReservations":
                        // existance of session variable & contents has been checked before
                        require_once('../roomsystem/reservation.class.php');
                        $rs = new reservation($pdo);
                        $reservations = $rs->getAllReservations();
                        if(!empty($reservations)) {
                            if($reservations['error'] == true) {
                                echo json_encode(array("success" => false, "message" => $reservations['message']));
                            } else {
                                echo json_encode(array("success" => true, "data" => $reservations['data'], "empty" => empty($reservations['data']) ));
                            }
                        } else {
                            echo json_encode(array("success" => false, "message" => "Es konnten keine Daten aus der Datenbank ausgelesen werden!"));
                        }
                        break;
                    case "deleteReservation":
                        if(isset($_POST['id'])) {

                            $rid = intval($_POST['id']);
                            require_once ('../roomsystem/reservation.class.php');
                            $rs = new reservation($pdo);
                            $res = $rs->deleteReservation($rid);

                            echo empty($res) ? json_encode(array("success" => false, "message" => "Es ist ein unbekannter Fehler beim Löschen der Reservierung aufgetreten!", "next" => "reload"))
                                : json_encode(array("success" => $res['error'], "message" => $res['error'] ? $res['message'] : "", "next" => "reload"));

                        } else {
                            echo json_encode(array("success" => false, "message" => "Es wurde keine zu löschende Reservierung angegeben!"));
                        }
                        break;
                    default:
                        echo json_encode(array("success" => false, "message" => "Die angeforderte Anfrage konnte nicht gefunden werden!"));
                        break;
                }
            } else {
                echo json_encode(array("success" => false, "message" => "Die angeforderte Abfrage konnte nicht gefunden werden!"));
            }

        } else {
            echo json_encode(array("success" => false, "message" => "Sie müssen ein aktiviertes Lehrerkonto besitzen, um Zugriff auf diese Seite zu erhalten!", "sessionError" => true));
        }
    }
<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 06.03.17
     * Time: 19:41
     */

    /**
     * 'reservation-api.php'
     * public file to be used as main api for any
     * VPlan includes via GET-Requests
     */

    /** Set encoding to UTF-8 */
    header('Content-Type: application/json; charset=utf-8');

    /**
     * Initialize Database connection
     * Edit this path to your dbconf file or define
     * your own $pdo - Variable
    */
    require_once ('../db/conf/dbconf.php');

    /**
     * @function throwError($m, $s)
     * @param $s string
     * @return string
     */
    function throwError($s) {
        $a = array(
            "error" => true,
            "message" => $s
        );

        /** If error-logging to database is needed, include it here, e.g. */

        return json_encode($a);
    }

    /**
     * @function validate($s)
     * @param $s string
     * @return string
     */
    function validate($s) {
        // Function to validate a custom user input

        // Replace any non-word-character
        $temp = preg_replace('/[^\w+|Ö|ö|Ü|ü|Ä|ä|ß]|_/', '', $s);
        // Lower the letters so the person using this to fetch information
        // does not need to care about upper- and lowercase typing
        return strtolower($temp);
    }

    /** Check if PDO is initialized, otherwise throw error */
    if(isset($pdo) && is_a($pdo, 'PDO')) {

        /** Check if needed GET-Information is set */
        if(isset($_GET['get'])) {
            $get = validate($_GET['get']);

            /** Declare needed classes */
            require_once ('../accountsystem/userManagement.class.php');
            require_once ('../roomsystem/reservation.class.php');
            $reservation = new reservation($pdo);
            $um = new userManagement($pdo);

            switch($get) {
                case "reservations":
                    // Check if we should return all reservations or
                    // just the reservations for one specific teacher

                    $reservations = array();

                    $teacher = "";

                    if(isset($_GET['prename']) && isset($_GET['surname'])) {
                        $temp = validate($_GET['prename'])[0].validate($_GET['surname']);
                        if($um->isUserInDB($temp)) {
                            $teacher = $temp;
                        } else {
                            $teacher = false;
                            echo throwError('Es konnte kein Lehrer gefunden werden, auf den Ihre Suchanfrage zutrifft!');
                        }
                    } elseif (isset($_GET['teacher'])) {
                        $teacher = validate($_GET['teacher']);
                    } elseif (isset($_GET['short'])) {
                        $res = $um->getTeacherByShortName(validate($_GET['short']));
                        if($res !== false) {
                            $teacher = $res['lehrer_accname'];
                        } else {
                            $teacher = false;
                            echo throwError('Das gesuchte Lehrerkürzel konnte nicht in der Datenbank gefunden werden!');
                        }
                    } else {
                        $teacher = "";
                    }

                    $max = "";

                    if(isset($_GET['max'])) {
                        $max = is_numeric(validate($_GET['max'])) ? validate($_GET['max']) : "";
                    }

                    if($teacher != "") {
                        $res = $reservation->getReservationsByName($teacher);
                        if(isset($res['data']) && $max != "" && $max <= count($res['data']) && $max > 0) {
                            $data = array();
                            for($i = 0; $i < $max; $i++) {
                                array_push($data, $res['data'][$i]);
                            }
                            $res['data'] = $data;
                            echo json_encode($res);
                        } else {
                            echo json_encode($res);
                        }
                    } elseif ($teacher !== false) {
                        /** Get All Reservations */

                        /** Check if a max response size is supplied and apply it to the response */
                        $res = $reservation->getAllReservations();
                        if(isset($res['data']) && $max != "" && $max <= count($res['data']) && $max > 0) {
                            $data = array();
                            for($i = 0; $i < $max; $i++) {
                                array_push($data, $res['data'][$i]);
                            }
                            $res['data'] = $data;
                            echo json_encode($res);
                        } else {
                            echo json_encode($res);
                        }
                    }

                    break;
                default:
                    // Even though it should not contain any html chars now,
                    // we pass it through htmlentities to make sure no HTML injection
                    // is possible in any way
                    echo throwError('Die geforderte Anfrage wurde nicht gefunden ('.htmlentities($get, ENT_QUOTES).')');
            }

        } else {
            echo throwError('Es wurden nicht alle benötigten Daten angegeben!');
        }

        /** fetch and validate $_GET information */

    } else {

    }
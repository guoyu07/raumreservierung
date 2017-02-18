<?php

    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 07.02.17
     * Time: 23:37
     */
    class reservation
    {
        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        /** Utility Functions */

        private function time_to_unix($t) {
            return date('Y-m-d H:i:s', $t);
        }

        /** Room Reservation Public Functions */

        public function getReservableRooms() {
            $sql = "SELECT * FROM plan_raeume WHERE reservable=1 ORDER BY raum_kurz";
            $r = $this->pdo->prepare($sql);
            $r->execute();
            $res = $r->fetchAll();
            return !empty($res) ? $res : false;
        }

        public function getTimeList($name, $weekday, $raumid, $date) {

            require_once('../accountsystem/userManagement.class.php');
            $um = new userManagement($this->pdo);

            if($um->isUserInDB($name) && $um->isUserActivated($name)) {

                $nameData = $um->getFullName($name);
                if(is_array($nameData)) {
                    // Go on with data reading
                    $lehrer_kurz = $nameData['lehrer_kurz'];
                    $roomData = $this->getRoomData($raumid);
                    $raum_kurz = $roomData['raum_kurz'];

                    $sql = "SELECT * FROM stupla_r WHERE raum_kurz=:rkurz AND tag=:weekday";
                    $r = $this->pdo->prepare($sql);
                    $r->execute(array(":rkurz" => $raum_kurz, ":weekday" => $weekday));
                    $res = $r->fetchAll();

                    /** Select Reservations for defined dates */
                    $sql2 = "SELECT * FROM reservations
                            WHERE reservations.tag=:weekday
                            AND reservations.raum_kurz=:rkurz
                            AND reservations.datum=:datum";
                    $r2 = $this->pdo->prepare($sql2);
                    $r2->execute(array(":weekday" => $weekday, ":rkurz" => $raum_kurz, ":datum" => $date));
                    $res2 = $r2->fetchAll();

                    $final = array_merge($res, $res2);

                    return empty($final) ? array("error" => false, "list" => array())
                        : array("error" => false, "list" => $final);

                } else {
                    // Dont Proceed, we need the short name
                    return array("error" => true, "message" => "Ihr Account ist nicht vollständig in die Datenbank eingetragen. Bitte kontaktieren Sie unbedingt einen Administrator!");
                }

            } else {
                return array("error" => true, "message" => "Der Nutzer konnte nicht in der Datenbank gefunden werden!");
            }

        }

        public function getRoomData($id) {
            if($id > 0) {
                $sql = "SELECT * FROM plan_raeume WHERE raumid=:id";
                $r = $this->pdo->prepare($sql);
                $r->execute(array(":id" => $id));
                $res = $r->fetchAll();
                return empty($res) ? false : $res[0];
            } else {
                return false;
            }
        }

        private function isRoomValid($rkurz, $weekday, $date, $stunde) {
            // If the SQL calls return any values, the specified room is not available
            // for reservation at the specified date, day, or lesson
            // Otherwise, the result array will be empty

            // 1. Check in stupla_r
            $sql = "SELECT * FROM stupla_r
                    WHERE stupla_r.raum_kurz=:rkurz
                    AND stupla_r.stunde=:stunde
                    AND stupla_r.tag=:weekday
                    LIMIT 1";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":rkurz" => $rkurz, ":weekday" => $weekday, ":stunde" => $stunde));

            // 2. Check in reservations
            $sql2 = "SELECT * FROM reservations
                    WHERE reservations.datum=:datum
                    AND reservations.tag=:weekday
                    AND reservations.stunde=:stunde
                    AND reservations.raum_kurz=:rkurz
                    LIMIT 1";
            $r2 = $this->pdo->prepare($sql2);
            $r2->execute(array(":datum" => $date, ":weekday" => $weekday, ":stunde" => $stunde, ":rkurz" => $rkurz));

            // The Room is valid when both calls return empty results
            // Even if only one of them returns a result, the room is
            // not available for reservation
            return (empty($r->fetchAll()) && empty($r2->fetchAll()));
        }

        public function reserveRoom($name, $raumid, $stunde, $weekday, $date) {
            require_once('../accountsystem/userManagement.class.php');
            $um = new userManagement($this->pdo);

            if($um->isUserInDB($name) && $um->isUserActivated($name)) {
                $nameData = $um->getFullName($name);
                $roomData = $this->getRoomData($raumid);
                if(is_array($nameData)) {
                    $lkurz = $nameData['lehrer_kurz'];
                    if(!is_array($roomData) || $roomData == false) {
                        return array("error" => true, "message" => "Die übermittelte Raum-ID ist ungültig!");
                    } else {
                        // Reserve Room
                        $rkurz = $roomData['raum_kurz'];
                        if($this->isRoomValid($rkurz, $weekday, $date, $stunde)) {

                            // Insert new reservation into database
                            $sql = "INSERT INTO reservations (lehrer_kurz, datum, tag, stunde, raum_kurz) VALUES (:lkurz, :datum, :weekday, :stunde, :rkurz)";
                            $r = $this->pdo->prepare($sql);
                            try {
                                $this->pdo->beginTransaction();
                                $r->execute(array(":lkurz" => $lkurz, ":datum" => $date, ":weekday" => $weekday, ":stunde" => $stunde, ":rkurz" => $rkurz));
                                $this->pdo->commit();
                                return array("error" => false);
                            } catch (PDOException $e) {
                                $this->pdo->rollBack();
                                return array("error" => true, "message" => $e->getMessage());
                            }
                        } else {
                            return array("error" => true, "message" => "Der Raum konnte nicht reserviert werden, da er zum angegebenen Zeitpunkt bereits belegt ist!");
                        }
                    }
                } else {
                    return array("error" => true, "message" => "Ihr Account ist nicht vollständig in die Datenbank eingetragen. Bitte kontaktieren Sie unbedingt einen Administrator!");
                }
            } else {
                return array("error" => true, "message" => "Der Nutzer konnte nicht in der Datenbank gefunden werden!");
            }
        }

        public function getReservationsByName($name) {
            require_once ('../accountsystem/userManagement.class.php');
            $um = new userManagement($this->pdo);
            if($um->isUserInDB($name) && $um->isUserActivated($name)) {

                $sql = "SELECT reservations.*, plan_lehrer.lehrer_accname FROM reservations, plan_lehrer
                        WHERE reservations.lehrer_kurz = plan_lehrer.lehrer_kurz
                        AND plan_lehrer.lehrer_accname = :accname
                        ORDER BY reservations.datum ASC";

                $r = $this->pdo->prepare($sql);

                try {
                    $this->pdo->beginTransaction();
                    $r->execute(array(":accname" => $name));
                    $this->pdo->commit();
                    $res = $r->fetchAll();

                    if(!empty($res)) {
                        $days = array(1 => "Montag", 2 => "Dienstag", 3 => "Mittwoch", 4 => "Donnerstag", 5 => "Freitag");
                        for($i = 0; $i<count($res); $i++) {
                            $dp = explode('-', $res[$i]['datum']);
                            $res[$i]['datum'] = $dp[2].".".$dp[1].".".$dp[0];
                            $res[$i]['dayString'] = $days[$res[$i]['tag']];
                        }
                    }

                    return array("error" => false, "data" => $res);
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    return array("error" => true, "message" => $e->getMessage());
                }

                /**
                 * Whoever reads this comment:
                 * https://youtu.be/US9HvJIBw5k?t=8m6s
                 * Thank me later <3
                 */

            } else {
                return array("error" => true, "message" => "Der Nutzeraccount konnte nicht gefunden werden oder ist nicht aktiviert!");
            }
        }

        public function deleteReservation($rid) {
            $sql = "DELETE FROM reservations WHERE R_ID=:rid";
            $r = $this->pdo->prepare($sql);
            try {
                $this->pdo->beginTransaction();
                $r->execute(array(":rid" => $rid));
                $this->pdo->commit();
                return array("error" => false);
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Es ist ein Fehler bei der Datenbankabfrage aufgetreten!");
            }
        }
    }
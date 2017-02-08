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
    }
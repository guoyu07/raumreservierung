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
            echo json_encode(array("success" => true, "fullname" => $fullname, "lehrer_kurz" => $lehrer_kurz));

        } else {
            echo json_encode(array("success" => false, "message" => "Sie müssen ein aktiviertes Lehrerkonto besitzen, um Zugriff auf diese Seite zu erhalten!", "sessionError" => true));
        }
    }
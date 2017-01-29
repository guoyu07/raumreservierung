<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 29.01.17
     * Time: 22:52
     */

    require_once('../backend/accountsystem/sessioncontroller.class.php');
    require_once('../backend/db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    require_once ('../backend/accountsystem/userManagement.class.php');
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
<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 18.12.16
     * Time: 21:40
     */

    require_once('sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);
    $sess->initialize();

    if(isset($_POST['name']) && isset($_POST['pw'])){
        $name = htmlentities($_POST['name'], ENT_QUOTES);
        $pw = htmlentities($_POST['pw'], ENT_QUOTES);

        require_once('sessionsystem.class.php');
        $session = new SessionSystem($name, $pw, $pdo);

        $result = $session->login();

        if(!isset($result['error'])){
            if($result['login'] === true){
                echo json_encode(array("success" => true, "type" => $_SESSION['acctype']));
            } else {
                echo json_encode(array("success" => false, "message" => $result['message']));
            }
        } else {
            echo json_encode(array("success" => false, "message" => $result['message']));
        }

    } elseif(isset($_POST['request'])){

        if($_POST['request'] == "logout") {
            $sess->logout();
        }

    } else {
        echo json_encode(array("success" => false, "message" => "Fehler: Es konnten nicht alle Daten &uuml;bermittelt werden!"));
    }
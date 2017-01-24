<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 18.12.16
     * Time: 21:40
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);
    // $sess->initialize(); Old Line, keeping it as reminder not to use it anymore :D

    if(isset($_POST['name']) && isset($_POST['pw']) && isset($_POST['ampcode'])){
        $name = $_POST['name'];
        $pw = $_POST['pw'];
        $code = $_POST['ampcode'];

        $name = convertAMP($name, $code);
        $pw = convertAMP($pw, $code);

        require_once('../accountsystem/loginsystem.class.php');
        $login = new loginSystem($name, $pw, $pdo);

        $result = $login->login();

        if(!isset($result['error'])){
            if($result['login'] === true){
                echo json_encode(array("success" => true, "type" => $_SESSION['acctype'], "status" => $_SESSION['accstatus']));
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

    function convertAMP($s, $c){
        return preg_replace("/::$c::/", "&", $s);
    }
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

    if(isset($_POST['name']) && isset($_POST['pw']) && isset($_POST['ampcode']) && $_SESSION['loggedin'] != true){
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

        switch($_POST['request']){
            case "logout":
                $sess->logout();
                break;
            case "resetPassword":
                if(isset($_POST['name']) && isset($_POST['email'])) {

                    require_once('../accountsystem/userManagement.class.php');
                    $um = new userManagement($pdo);

                    echo json_encode(array("message" => $um->sendPasswordResetMail($_POST['name'], $_POST['email'])['message']));

                } else {
                    echo json_encode(array("success" => false, "message" => "Es wurden nicht alle benötigten Daten angegeben!"));
                }
                break;
        }

    } else {
        echo json_encode(array("success" => false, "message" => "Fehler: Es konnten nicht alle Daten &uuml;bermittelt werden!"));
    }

    function convertAMP($s, $c){
        return preg_replace("/::$c::/", "&", $s);
    }
<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 23.01.17
     * Time: 22:25
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(isset($_SESSION['name']) && $_SESSION['loggedin'] == true && $_SESSION['acctype'] == 1 && $_SESSION['accstatus'] == 3){
        if(isset($_POST['request'])) {
            switch($_POST['request']) {
                case "getErrors":

                    require_once('../accountsystem/userManagement.class.php');
                    require_once('../db/conf/dbconf.php');
                    $um = new userManagement($pdo);

                    $errorList = $um->getErrors();

                    $errors = !empty($errorList['data']);

                    if($errorList['error'] === false) {
                        echo json_encode(array("success" => true, "submissions" => $errorList['data'], "errors" => $errors));
                    } else {
                        echo json_encode(array("success" => false, "message" => $errorList['message']));
                    }

                    break;
                case "deleteError":

                    if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && isset($_SESSION['name']) && $_SESSION['acctype'] == 1 && isset($_SESSION['accstatus']) == 3){
                        if(isset($_POST['id'])) {
                            if(is_numeric($_POST['id']) && is_int(intval($_POST['id']))) {

                                require_once('../accountsystem/userManagement.class.php');
                                require_once('../db/conf/dbconf.php');
                                $um = new userManagement($pdo);

                                $id = intval($_POST['id']);

                                $res = $um->deleteError($id);

                                $errors = !empty($res['data']);

                                if($res['error'] === false) {
                                    echo json_encode(array("success" => true, "submissions" => $res['data'], "errors" => $errors));
                                } else {
                                    echo json_encode(array("success" => false, "message" => $res['message']));
                                }

                            } else {
                                echo json_encode(array("success" => false, "message" => "Die Error-ID muss numerisch sein!"));
                            }
                        } else {
                            echo json_encode(array("success" => false, "message" => "Es wurden nicht alle benötigten Daten angegeben!"));
                        }
                    } else {
                        json_encode(array("success" => false, "message" => "Sie müssen als Administrator eingeloggt sein, um diese Aktion ausführen zu können!"));
                    }
                    break;
                default:
                    echo json_encode(array("success" => false, "message" => "Die Anfrage konnte nicht gefunden werden!"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Es sind zu wenig Daten angegeben worden!"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Sie müssen (als Administrator) eingeloggt sein, um diese Aktion durchführen zu können!", "sessionError" => true));
    }
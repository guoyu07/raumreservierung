<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 29.01.17
     * Time: 16:13
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    // User should not be logged in when wanting to reset his password???
    if($_SESSION)
        if($_SESSION['loggedin'] == true)
            echo json_encode(array("success" => false, "message" => "Sie können keine Passwortwiederherstellung anfordern, solange Sie angemeldet sind!"));
        else {
            // Main Code
            if(isset($_POST['name']) && isset($_POST['code']) && isset($_POST['request'])) {

                switch($_POST['request']) {
                    case "validateQuery":
                        // Data usually doesnt contain any special characters, checking this to prevent injections & stuff
                        $name   = preg_replace('/[^\w+|Ö|ö|Ä|ä|Ü|ü|ß]|_|[S+]/', '', $_POST['name']);
                        $code = htmlentities($_POST['code'], ENT_QUOTES);

                        if(strlen($name) > 4 && strlen($code) == 64) {
                            // DB check
                            require_once ('../accountsystem/userManagement.class.php');
                            $um = new userManagement($pdo);
                            if($um->isUserInDB($name)){

                                if($um->isUserActivated($name)) {
                                    $res = $um->validateQuery($name, $code);
                                    if(!empty($res)) {
                                        if($res['error'] == true) {
                                            echo json_encode(array("success" => false, "message" => $res['message']));
                                        } else {
                                            echo json_encode(array("success" => true, "validateQuery" => true));
                                        }
                                    } else {
                                        echo json_encode(array("success" => false, "message" => "Es ist ein Fehler beim Überprüfen der Daten aufgetreten!"));
                                    }
                                } else {
                                    echo json_encode(array("success" => false, "message" => "Der Account muss aktiviert sein, damit sein Passwort zurückgesetzt werden kann!"));
                                }

                            } else {
                                echo json_encode(array("success" => false, "message" => "Die übergebenen Werte sind ungültig!"));
                            }

                        } else {
                            echo json_encode(array("success" => false, "message" => "Die übergebenen Werte sind ungültig!"));
                        }
                        break;
                    case "changePassword":

                        if(isset($_POST['password'])) {
                            // Data usually doesnt contain any special characters, checking this to prevent injections & stuff
                            $name   = preg_replace('/[^\w+|Ö|ö|Ä|ä|Ü|ü|ß]|_|[S+]/', '', $_POST['name']);
                            $code = htmlentities($_POST['code'], ENT_QUOTES);
                            $pw = preg_replace("/::AMP::/", "&", $_POST['password']);

                            if(strlen($name) > 4 && strlen($code) == 64 && strlen($pw) >= 6) {

                                // DB check
                                require_once ('../accountsystem/userManagement.class.php');
                                $um = new userManagement($pdo);
                                if($um->isUserInDB($name)){

                                    if($um->isUserActivated($name)) {

                                        $res = $um->validateQuery($name, $code);

                                        if($res['error'] != true) {

                                            // Change PW
                                            $res = $um->resetPassword($name, $pw);
                                            if($res['error'] == false) {
                                                echo json_encode(array("success" => true, "changePassword" => true));
                                            } else {
                                                echo json_encode(array("success" => false, "message" => $res['message']));
                                            }

                                        } else {
                                            echo json_encode(array("success" => false, "message" => $res['message']));
                                        }

                                    } else {
                                        echo json_encode(array("success" => false, "message" => "Der zurückzusetzende Account muss aktiviert sein!"));
                                    }

                                } else {
                                    echo json_encode(array("success" => false, "message" => "Die übergebenen Werte sind ungültig!"));
                                }

                            } else {
                                echo json_encode(array("success" => false, "message" => "Die übergebenen Werte sind ungültig!"));
                            }
                        } else {
                            echo json_encode(array("success" => false, "message" => "Sie müssen ein neues Passwort angeben!"));
                        }

                        break;
                    default:
                        echo json_encode(array("success" => false, "message" => "Die geforderte Abfrage konnte nicht gefunden werden!"));
                        break;
                }

            } else {
                echo json_encode(array("success" => false, "message" => "Es wurden nicht alle benötigten Daten übertragen!"));
            }
        }
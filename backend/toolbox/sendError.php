<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 09.01.17
     * Time: 01:08
     */

    require_once ('../db/conf/dbconf.php');

        if(isset($_POST['request']) && isset($_POST['msg'])){

            if($_POST['request'] == "error")
            {

                if(isset($_POST['name'])){

                    $name = $_POST['name'];
                    $msg = substr($_POST['msg'], 0, 2048);
                    $page = (isset($_POST['page']) && $_POST['page'] != "") ? $_POST['page'] : null;

                    if($name != "" && $msg != "") {
                        $sql = "INSERT INTO errorreport (name, email, page, text) VALUES (:accname, NULL, :epage, :msg)";
                        $r = $pdo->prepare($sql);

                        try {
                            $pdo->beginTransaction();
                            $r->execute(array(":accname" => $name, ":epage" => $page, ":msg" => $msg));
                            $pdo->commit();
                            echo json_encode(array("success" => true, "message" => "Ihre Fehlermeldung wurde erfolgreich abgesendet.<br>Vielen Dank!"));
                        } catch (PDOException $e) {
                            $pdo->rollBack();
                            echo json_encode(array("success" => false, "message" => "Es ist ein Fehler bei der Datenbankverbindung aufgetreten:<br>".$e->getMessage()));
                        }

                    } else {
                        echo json_encode(array("success" => false, "message" => "Die angegebenen Daten sind ung&uuml;ltig!"));
                    }

                } elseif (isset($_POST['email'])) {

                    $email = $_POST['email'];
                    $msg = substr($_POST['msg'], 0, 2048);
                    $page = (isset($_POST['page']) && $_POST['page'] != "") ? $_POST['page'] : null;

                    if($email != "" && $msg != "") {
                        $sql = "INSERT INTO errorreport (name, email, page, text) VALUES (NULL, :email, :epage, :msg)";
                        $r = $pdo->prepare($sql);

                        try {
                            $pdo->beginTransaction();
                            $r->execute(array(":email" => $email, ":epage" => $page, ":msg" => $msg));
                            $pdo->commit();
                            echo json_encode(array("success" => true, "message" => "Ihre Fehlermeldung wurde erfolgreich abgesendet.<br>Vielen Dank!"));
                        } catch (PDOException $e) {
                            $pdo->rollBack();
                            echo json_encode(array("success" => false, "message" => "Es ist ein Fehler bei der Datenbankverbindung aufgetreten:<br>".$e->getMessage()));
                        }
                    } else {
                        echo json_encode(array("success" => false, "message" => "Die angegebenen Daten sind ung&uuml;ltig!"));
                    }

                } else {
                    echo json_encode(array("success" => false, "message" => "Es wurden nicht alle Daten &uuml;bermittelt!"));
                }
            } else {
                echo json_encode(array("success" => false, "message" => "Es ist ein Fehler bei der &Uuml;bergabe der Daten aufgetreten!"));
            }

        } else {
            echo json_encode(array("success" => false, "message" => "Bitte geben Sie die ben&ouml;tigten Daten f&uuml;r diese Abfrage an!"));
        }
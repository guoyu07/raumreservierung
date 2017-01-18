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

                    $name = decodeText($_POST['name']);
                    $msg = decodeText(htmlentities($_POST['msg'], ENT_QUOTES));
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

                    $email = decodeText($_POST['email']);
                    $msg = decodeText(htmlentities($_POST['msg'], ENT_QUOTES));
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

    function decodeText($str) {
        // Dirty but functionally... This honestly makes myself wanting to kill myself ._.
        $str = preg_replace("/::AMP::/", "&amp;", $str);
        $str = preg_replace("/::LT::/", "&lt;", $str);
        $str = preg_replace("/::GT::/", "&gt;", $str);
        $str = preg_replace("/::QUOT::/", "&quot;", $str);

        return $str;
    }
<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 01.02.17
     * Time: 18:09
     */

    require_once ('../accountsystem/sessioncontroller.class.php');
    require_once ('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(!isset($_SESSION) || isset($_SESSION['loggedin'])) {
        if($_SESSION['loggedin'] != false || $_SESSION['acctype'] != 1 || $_SESSION['accstatus'] != 3) {
            if(isset($_POST['request'])) {
                switch($_POST['request']) {
                    case "getLogs":
                        // Get all Log Messages
                        $sql = "SELECT * FROM cronjob ORDER BY created DESC LIMIT 20";
                        $r = $pdo->prepare($sql);
                        try {
                            $pdo->beginTransaction();
                            $stat = $r->execute();
                            $pdo->commit();
                            if($stat) {
                                $res = $r->fetchAll();
                                if(!empty($res)) {
                                    for($i=0;$i<count($res);$i++) {
                                        $res[$i]['cron_log'] = preg_replace("/\\n|\\r\\n/", "<br>", htmlentities($res[$i]['cron_log']));
                                        $res[$i]['created'] = date('d.m.Y - H:i', strtotime($res[$i]['created']))." Uhr";
                                        $res[$i]['changes'] = ($res[$i]['changes'] == 0) ? false : true;
                                    }
                                } else {
                                    $res = false;
                                }
                                echo json_encode(array("success" => true, "logs" => $res));
                            } else {
                                echo json_encode(array("success" => false, "message" => "Bei der Datenabfrage ist ein unbekannter Fehler aufgetreten!"));
                            }
                        } catch (PDOException $e) {
                            $pdo->rollBack();
                            echo json_encode(array("success" => false, "message" => $e->getMessage()));
                        }
                        break;
                    case "deleteLog":
                        // Delete specified log entry
                        if(isset($_POST['id'])) {
                            if(is_int(intval($_POST['id']))) {
                                $sql = "DELETE FROM cronjob WHERE C_ID=:id";
                                $r = $pdo->prepare($sql);
                                try {
                                    $pdo->beginTransaction();
                                    $stat = $r->execute(array(":id" => intval($_POST['id'])));
                                    $pdo->commit();
                                    echo json_encode(array("success" => $stat, "message" => $stat ? "" : "Es ist ein Fehler bei der Datenbankabfrage aufgetreten", "next" => $stat ? "reload" : ""));
                                } catch (PDOException $e) {
                                    $pdo->rollBack();
                                    echo json_encode(array("success" => false, "message" => $e->getMessage()));
                                }
                            } else {
                                echo json_encode(array("success" => false, "message" => "Die angegebene Log-ID ist ungültig!"));
                            }
                        } else {
                            echo json_encode(array("success" => false, "message" => "Es wurde kein zu löschender Log-Eintrag angegeben!"));
                        }
                        break;
                    case "deleteAllLogs":
                        // user executing this needs DROP_PRIV to be enabled
                        $sql = "TRUNCATE cronjob";
                        $r = $pdo->prepare($sql);
                        try {
                            $pdo->beginTransaction();
                            $r->execute();
                            $pdo->commit();
                            echo json_encode(array("success" => true, "next" => "reload"));
                        } catch (PDOException $e) {
                            $pdo->rollBack();
                            echo json_encode(array("success" => false, "message" => $e->getMessage()));
                        }
                        break;
                    default:
                        echo json_encode(array("success" => false, "message" => "Die geforderte Abfrage konnte nicht gefunden werden!"));
                }
            } else {
                echo json_encode(array("success" => false, "message" => "Es wurde keine Abfrage angegeben!"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Sie müssen (als Administrator) eingeloggt sein, um diese Daten abrufen zu können!", "sessionError" => true));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Es konnte keine Sitzung initialisiert werden. Überprüfen Sie ggf. bitte die Cookie-Präferenzen Ihres Browsers!", "sessionError" => true));
    }
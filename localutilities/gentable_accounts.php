<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 08.01.17
     * Time: 21:07
     */

    header('Content-Type: text/html; charset=utf-8');

    require_once ('../backend/db/conf/dbconf.php');

    $loadTime = time();

    $sql = "SELECT lehrer_vorname, lehrer_nachname FROM plan_lehrer";
    $res = $pdo->query($sql)->fetchAll();

    $data = array();

    foreach($res as $lehrer){
        $vor = strtolower($lehrer['lehrer_vorname']);
        $nach = strtolower($lehrer['lehrer_nachname']);

        $pw = "gykl@".$vor;
        $username = $vor[0].$nach;

        require_once ('../backend/accountsystem/userManagement.class.php');
        $um = new userManagement($pdo);
        //Uncomment to activate
        //$res = $um->addUser($username, $pw, 3, 1);


        if($res['error'] != false){
            echo "Error: ".$res['message']."<br>";
            echo "<br>Das Skript wurde unerwartet beendet, alle bisherigen &Auml;nderungen wurden r&uuml;ckg&auml;ngig gemacht!<br>";
        } else {
            echo "[GENTABLE] - Added Teacher \"$username\"<br>";
        }
    }

    $stopTime = time();
    echo "<hr>";
    echo "Done!<br>";
    echo "Execution Time: ".date('s', $stopTime - $loadTime)."s<br>";
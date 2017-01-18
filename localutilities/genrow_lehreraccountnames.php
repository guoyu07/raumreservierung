<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 10.01.17
     * Time: 23:15
     */
    require_once ('../backend/db/conf/dbconf.php');

    $sql = "SELECT lehrer_vorname, lehrer_nachname FROM plan_lehrer";
    $res = $pdo->query($sql)->fetchAll();

    foreach($res as $lehrer){

        $vor = $lehrer['lehrer_vorname'];
        $nach = $lehrer['lehrer_nachname'];

        $accname = strtolower($vor[0].$nach);

        $sql = "UPDATE raumreservierung.plan_lehrer SET lehrer_accname=:accname
                WHERE lehrer_vorname=:vorname
                AND lehrer_nachname=:nachname";

        $r = $pdo->prepare($sql);
        try{
            $pdo->beginTransaction();
            $r->execute(array(":accname" => $accname, ":vorname" => $vor, ":nachname" => $nach));
            $pdo->commit();
            echo "$accname ... DONE<br>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "Error while updating:<br>".$e->getMessage();
        }

    }

    echo "<hr>I DID MY JOB MOTHERFUCKER :)";
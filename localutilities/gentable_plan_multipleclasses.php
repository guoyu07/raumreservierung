<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 08.01.17
     * Time: 14:54
     * EXISTANT FOR TESTING PURPOSES ONLY!
     */

    $loadTime = time();

    $plan = simplexml_load_file("splanr.xml");

    $stupla = array();

    foreach($plan->element as $e){

        $raumnr = $e->kopf->titel;

        foreach($e->haupt->zeile as $z){

            array_push($stupla, array(
                "raumnr" => $raumnr,
                "stunde" => $z->stunde,
                "tage" => array(
                    "1" => $z->tag1,
                    "2" => $z->tag2,
                    "3" => $z->tag3,
                    "4" => $z->tag4,
                    "5" => $z->tag5
                )
            ));

        }
    }

    foreach($stupla as $raum){

        $nr = $raum['raumnr'];
        $stunde = $raum['stunde'];
        $tag1klassen = is_object($raum['tage']['1']) ? explode(" ", $raum['tage']['1']->klasse) : array();
        $tag2klassen = is_object($raum['tage']['2']) ? explode(" ", $raum['tage']['2']->klasse) : array();
        $tag3klassen = is_object($raum['tage']['3']) ? explode(" ", $raum['tage']['3']->klasse) : array();
        $tag4klassen = is_object($raum['tage']['4']) ? explode(" ", $raum['tage']['4']->klasse) : array();
        $tag5klassen = is_object($raum['tage']['5']) ? explode(" ", $raum['tage']['5']->klasse) : array();


        require_once('../backend/db/conf/dbconf.php');

        $sql = "INSERT INTO plan_multipleclasses (raum_kurz, klasse, tag, stunde) VALUES (:raum, :klasse, :tag, :stunde)";
        /** Uncomment to activate */
        //$r = $pdo->prepare($sql);

        foreach($tag1klassen as $klasse){
            if($klasse != ""){
                $ex = array(
                    ":raum" => $nr,
                    ":klasse" => $klasse,
                    ":tag" => 1,
                    ":stunde" => $stunde
                );
                $r->execute($ex);
            }
        }

        foreach($tag2klassen as $klasse){
            if($klasse != ""){
                $ex = array(
                    ":raum" => $nr,
                    ":klasse" => $klasse,
                    ":tag" => 2,
                    ":stunde" => $stunde
                );
                $r->execute($ex);
            }
        }
        foreach($tag3klassen as $klasse){
            if($klasse != ""){
                $ex = array(
                    ":raum" => $nr,
                    ":klasse" => $klasse,
                    ":tag" => 3,
                    ":stunde" => $stunde
                );
                $r->execute($ex);
            }
        }
        foreach($tag4klassen as $klasse){
            if($klasse != ""){
                $ex = array(
                    ":raum" => $nr,
                    ":klasse" => $klasse,
                    ":tag" => 4,
                    ":stunde" => $stunde
                );
                $r->execute($ex);
            }
        }
        foreach($tag5klassen as $klasse){
            if($klasse != ""){
                $ex = array(
                    ":raum" => $nr,
                    ":klasse" => $klasse,
                    ":tag" => 5,
                    ":stunde" => $stunde
                );
                $r->execute($ex);
            }
        }

    }

    $stopTime = time();

    echo "Done!<br>";
    echo "Execution time: " . date('s', $stopTime - $loadTime) . " Sekunden<br>";
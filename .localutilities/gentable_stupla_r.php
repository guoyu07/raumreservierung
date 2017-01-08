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

        $tag = 1;

            if(count($raum['tage'][$tag]) > 1){
                $tag1lehrer = array();
                foreach($raum['tage'][$tag] as $ra) {
                    array_push($tag1lehrer, $ra->lehrer);
                }
            } else {
                $tag1lehrer = is_object($raum['tage'][$tag]) ? $raum['tage'][$tag]->lehrer : null;
            }

            $tag1fach = is_object($raum['tage'][$tag]) ? $raum['tage'][$tag]->fach : null;

            require_once('../backend/db/conf/dbconf.php');

            $sql = "INSERT INTO stupla_r (raum_kurz, stunde, tag, fach_kurz, lehrer_kurz, woche)
                VALUES (:raum, :stunde, :tag, :fach, :lehrer, :woche)";
            $r = $pdo->prepare($sql);

            //Tag 1
            $raum = $nr;
            $fach = $tag1fach;
            $lehrer = $tag1lehrer;

            if($lehrer != null && $fach != null) {

                foreach($lehrer as $l) {
                    if(preg_match('/\*\*/', $l)){
                        $woche = "b";
                        $l = str_replace('*', '', $l);
                    } elseif (preg_match('/\*/', $l)) {
                        $woche = "a";
                        $l = str_replace('*', '', $l);
                    } else {
                        $woche = null;
                    }
                    /** Uncomment to activate */
                    //$r->execute(array(":raum" => $raum, ":stunde" => $stunde, ":tag" => $tag, ":fach" => $fach, ":lehrer" => $l, ":woche" => $woche));
                }
            }

    }

    $stopTime = time();

    echo "Done for day $tag (Change \$tag to 1, 2, 3, 4 or 5 [on line 42] for the other days!<br>";
    echo "Execution time: " . date('s', $stopTime - $loadTime) . " Sekunden<br>";
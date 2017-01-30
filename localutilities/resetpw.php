<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 27.01.17
     * Time: 01:11
     */

    require_once ('../backend/db/conf/dbconf.php');
    require_once ('../backend/accountsystem/userManagement.class.php');

    $um = new userManagement($pdo);

    $user = "notInDatabase";
    $pw = "RANDOM";
    echo "reset pw for user `$user`:<br>";
    var_dump($um->changePassword($user, $pw));
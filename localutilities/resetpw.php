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

    $user = "mmenzel";
    $pw = "gykl@2016";
    echo "reset pw for user `$user`:<br>";
    print_r($um->changePassword($user, $pw));
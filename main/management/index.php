<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 23.12.16
     * Time: 20:53
     */

    require_once('../../backend/accountsystem/sessioncontroller.class.php');
    require_once('../../backend/db/conf/dbconf.php');
    $sess = new SessionController($pdo);
    $sess->initialize();

    require_once('../../backend/db/conf/dbconf.php');
    require_once('../../backend/accountsystem/userManagement.class.php');

    /** EXAMPLE OF NEW REGISTRATION
    $manager = new userManagement($pdo);

    $name = "moritzmenzel";
    $pw = "gykl@2016";

    $result = $manager->addUser($name, $pw);

    print_r($result);
     */
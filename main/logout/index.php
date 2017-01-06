<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 30.12.16
     * Time: 17:15
     */

    require_once('../../backend/accountsystem/sessioncontroller.class.php');
    require_once('../../backend/db/conf/dbconf.php');
    $sess = new SessionController($pdo);
    $sess->logout();
    header('Location: /raumreservierung/project');
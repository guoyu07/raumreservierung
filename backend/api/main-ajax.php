<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 20.01.17
     * Time: 21:40
     */

    require_once ('../accountsystem/sessioncontroller.class.php');
    require_once ('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);
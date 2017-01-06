<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 23.12.16
     * Time: 12:30
     */

    // CONFIGURATION FILE FOR PDO::MYSQL_CONNECTION

    $dbname =   "raumreservierung";
    $host   =   "localhost";
    $dsn    =   'mysql:dbname='.$dbname.';host='.$host.';charset=utf8mb4';

    $user   =   "moritz";
    $pass   =   "spermasuppe";      // Insider Joke # No Comment :D

    try {
        $pdo = new PDO($dsn, $user, $pass);
    } catch (PDOException $error) {
        $pdo = 'Die Verbindung zur Datenbank ist fehlgeschlagen: ' . $error->getMessage();
    }
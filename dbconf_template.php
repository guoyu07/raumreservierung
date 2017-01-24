<?php

    // CONFIGURATION FILE FOR PDO::MYSQL_CONNECTION

    $dbname =   "NAME_OF_DATABASE";
    $host   =   "NAME_OF_HOST";
    $dsn    =   'mysql:dbname='.$dbname.';host='.$host.';charset=utf8mb4';

    $user   =   "USERNAME";
    $pass   =   "PASSWORD";

    try {
        $pdo = new PDO($dsn, $user, $pass);
    } catch (PDOException $error) {
        $pdo = 'Die Verbindung zur Datenbank ist fehlgeschlagen: ' . $error->getMessage();
    }
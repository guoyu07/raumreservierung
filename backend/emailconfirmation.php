<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 26.12.16
     * Time: 01:08
     */

    if(isset($_GET['name']) && isset($_GET['code']))
    {
        $code = htmlentities($_GET['code'], ENT_QUOTES);
        $name = htmlentities($_GET['name'], ENT_QUOTES);

        require_once('accountsystem/userManagement.class.php');
        require_once('db/conf/dbconf.php');
        $um = new userManagement($pdo);

        $result = $um->confirmUser($name, $code);

        $header = "";
        $msg = "";
        $error = "";

        if($result['error'] == true){
            $header = "Fehler!";
            $msg = "Bei der Best&auml;tigung Ihrer E-Mail - Adresse ist ein Fehler aufgetreten,<br>
                  bitte melden Sie sich bei einem der Administratoren oder versuchen Sie es erneut.<br><br>";
            $msg .= "Fehlermeldung:";
            $error = $result['message'];
        } elseif($result['error'] == false) {
            $header = "Herzlich Willkommen!";
            $msg = "Ihre E-Mail - Adresse wurde erfolgreich best&auml;tigt.<br>Viel Spa&szlig; bei der Benutzung der Raumreservierung :)<br><br>";
            $msg .= "Meldung des Servers:<br>".$result['message'];
        }

        echo <<<HTML

<!DOCTYPE html>
<html>

<head>
    <title>E-Mail - Best&auml;tigung</title>

    <!-- Webcomponents -->
    <script src="../bower_components/webcomponentsjs/webcomponents.min.js"></script>

    <!-- style imports -->
    <link rel="stylesheet" type="text/css" href="../style/main.css">
    <link rel="import" href="../style/main-polymer.css.html">

    <!-- Polymer Imports -->
    <link rel="import" href="../additional/imports.html">
    <link rel="import" href="../custom_elements/admin-nutzerverwaltung/admin-nutzerverwaltung.html">
    <link rel="import" href="../custom_elements/admin-raumverwaltung/admin-raumverwaltung.html">

    <!-- meta configuration -->
    <meta charset="UTF-8">
    <meta name="authors" content="Moritz Menzel, Maximilian Seiler, Benjamin Kirchhoff">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
</head>
<body style="font-family: Roboto, Verdana, sans-serif;margin: 0;">

    <paper-drawer-panel force-narrow right-drawer id="sidebar">
        <paper-header-panel drawer>

            <paper-toolbar style="background-color: #607D8B;color: white;">
                <paper-icon-button icon="arrow-back" paper-drawer-toggle title="Schlie&szlig;en"></paper-icon-button>
                <div class="title" style="text-align: center; margin-left: -15px;">Men&uuml;</div>
            </paper-toolbar>

            <paper-menu>
                <a href="/">
                    <paper-icon-item>
                        <iron-icon icon="home" item-icon></iron-icon>
                        Startseite
                    </paper-icon-item>
                </a>
                <a href="https://www.gymnasium-klotzsche.de/cont/cms/front_content.php?idcat=121">
                    <paper-icon-item>
                        <iron-icon icon="gavel" item-icon></iron-icon>
                        Impressum
                    </paper-icon-item>
                </a>
            </paper-menu>

        </paper-header-panel>
        <paper-header-panel main>

            <paper-toolbar id="header">
                <a href="https://www.gymnasium-klotzsche.de" target="_self"><img src="../img/gykl-logo.gif" width="80px" alt="Gymnasium Klotzsche" title="Gymnasium Klotzsche"></a>
                <div class="title">E-Mail - Best&auml;tigung</div>
                <paper-icon-button paper-drawer-toggle icon="more-vert"></paper-icon-button>
            </paper-toolbar>
            
            <paper-material elevation="1" style="text-align: center; margin: 0 auto; width: 90%; padding: 10px;">
            
                <h2>$header</h2>
                <p>
                    $msg<br>
                    <span style="color: red;">$error</span>
                </p>
                
                <br><br>
                
                <a href="/" style="color: white;"><paper-button style="background-color: #607D8B;color: white;"><iron-icon icon="home" style="margin-right: 10px;"></iron-icon>Startseite</paper-button></a>
            
            </paper-material>

        </paper-header-panel>

    </paper-drawer-panel>

</body>

</html>

HTML;


    } else { header('Location: /'); }
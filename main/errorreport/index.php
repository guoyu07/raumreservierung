<?php

    require_once ('../../backend/db/conf/dbconf.php');
    require_once ('../../backend/accountsystem/sessioncontroller.class.php');

    $sess = new SessionController($pdo);

    $loggedin = intval($_SESSION['loggedin']);
    if($_SESSION['loggedin'] == true){
        $name = $_SESSION['name'];
        $logoutlink = <<<HTML
<a href="/raumreservierung/project/main/logout">
    <paper-icon-item>
        <iron-icon icon="settings-power" item-icon></iron-icon>
        Ausloggen
    </paper-icon-item>
</a>
HTML;

    } else {
        $name = "";
        $logoutlink = "";
    }

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <title>Raumreservierung</title>

    <!-- Webcomponent Imports -->
    <script src="../../bower_components/webcomponentsjs/webcomponents.min.js"></script>

    <!-- style imports -->
    <link rel="stylesheet" type="text/css" href="../../style/landingpage-index.css">
    <link rel="stylesheet" type="text/css" href="../../style/main.css">
    <link rel="import" href="../../style/main-polymer.css.html">

    <!-- Polymer Imports -->
    <link rel="import" href="../../additional/imports.html">
    <link rel="import" href="../../custom_elements/reporting-form/reporting-form.html">

    <!-- meta configuration -->
    <meta charset="UTF-8">
    <meta name="authors" content="Moritz Menzel, Maximilian Seiler, Benjamin Kirchhoff">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
</head>
<body>

<style is="custom-style">
    paper-button {
        --paper-button: {
            @apply(--layout-horizontal);
            @apply(--layout-center-center);
            background-color: #8BC34A;
            color: #212121;
        };
    }
    paper-card {
        --paper-card: {
            @apply(--layout-vertical);
            width: auto;
            margin-top: 20px;
        }
    }
</style>

<platinum-https-redirect></platinum-https-redirect>

<iron-location dwell-time="-1" id="locator" url-space-regex="^/project/"></iron-location>

<paper-drawer-panel force-narrow right-drawer id="sidebar">
    <paper-header-panel drawer>

        <paper-toolbar style="background-color: #607D8B;color: white;">
            <paper-icon-button icon="arrow-back" paper-drawer-toggle title="Schlie&szlig;en"></paper-icon-button>
            <div class="title" style="text-align: center; margin-left: -15px;">Men&uuml;</div>
        </paper-toolbar>

        <paper-menu>
            <a href="/raumreservierung/project">
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
            <?=$logoutlink?>
        </paper-menu>

    </paper-header-panel>
    <paper-header-panel main>

        <paper-material elevation="2">
            <paper-toolbar id="header">
                <a href="https://www.gymnasium-klotzsche.de" target="_self"><img src="../../img/gykl-logo.gif" width="80px" alt="Gymnasium Klotzsche" title="Gymnasium Klotzsche"></a>
                <a href="" id="bclink"><div class="title shrink"u id="breadcrumb">Einen Fehler melden</div></a><h3 style="font-weight: normal;" id="bcaddition"></h3>
                <paper-icon-button icon="more-vert" title="Men&uuml; &ouml;ffnen" paper-drawer-toggle style="position: absolute; right: 12px;"></paper-icon-button>
            </paper-toolbar>
        </paper-material>

        <div id="content">

            <paper-card heading="Informationen zur Kontaktaufnahme">
                <div class="card-content">
                    Mit dem Nachfolgenden Formular haben Sie die M&ouml;glichkeit, einem Administrator eine Fehlermeldung zu senden.<br>
                    Um den Administratoren das Auswerten Ihres Problems etwas zu vereinfachen, achten Sie bitte auf folgende Dinge:<br>
                    <ul>
                        <li>Geben Sie bitte - wenn m&ouml;glich - die Seite an, auf der Sie einen Fehler gefunden haben. (Link)</li>
                        <li>Sollten Sie einen aktivierten Account f&uuml;r die Raumreservierung besitzen, geben Sie bitte dessen Namen an, andernfalls m&uuml;ssen Sie Ihre E-Mail - Adresse angeben.</li>
                        <li>Seien Sie bei der Formulierung des Problemes bitte so pr&auml;zise wie m&ouml;glich, um die Probleml&ouml;sung zu erleichtern.</li>
                        <li>Nutzen Sie das Formular bitte nur f&uuml;r ernsthafte Fehler, Missbrauch wird nicht geahndet, aber muss doch nicht sein ;)</li>
                    </ul>
                </div>
            </paper-card>

            <reporting-form loggedin="<?=$loggedin?>" name="<?=$name?>"></reporting-form>

        </div>

    </paper-header-panel>
</paper-drawer-panel>

</body>


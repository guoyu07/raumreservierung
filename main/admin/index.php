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

    if(!isset($_SESSION['loggedin']) || !isset($_SESSION['acctype']) || !isset($_SESSION['accstatus']))
    {
        $sess->logout();
        header('Location: /raumreservierung/project/');
    }
    elseif($_SESSION['loggedin'] === true && $_SESSION['acctype'] == 1 && $_SESSION['accstatus'] == 3) {

        //Main Content
        $name = $_SESSION['name'];

        echo <<<HTML
<!DOCTYPE html>
<html>

<head>
    <title>RR :: Global Admin</title>

    <!-- Webcomponents -->
    <script src="../../bower_components/webcomponentsjs/webcomponents.min.js"></script>

    <!-- style imports -->
    <link rel="stylesheet" type="text/css" href="../../style/main.css">
    <link rel="import" href="../../style/main-polymer.css.html">

    <!-- Polymer Imports -->
    <link rel="import" href="../../additional/imports.html">
    
    <link rel="import" href="../../custom_elements/admin-nutzerverwaltung/admin-nutzerverwaltung.html">
    <link rel="import" href="../../custom_elements/admin-raumverwaltung/admin-raumverwaltung.html">

    <!-- meta configuration -->
    <meta charset="UTF-8">
    <meta name="authors" content="Moritz Menzel, Maximilian Seiler, Benjamin Kirchhoff">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
</head>

<body>

<style is="custom-style">
    paper-tabs {
        --paper-tabs-selection-bar-color: #607D8B;
    }
    paper-tab {
        --paper-tab-ink: #607D8B;
    }
</style>

<platinum-https-redirect></platinum-https-redirect>
<iron-location dwell-time="1" id="locator" url-space-regex="^/admin/"></iron-location>

<paper-drawer-panel force-narrow right-drawer id="sidebar">
    <paper-header-panel drawer>

        <paper-toolbar style="background-color: #607D8B;color: white;">
            <paper-icon-button icon="arrow-back" paper-drawer-toggle title="Schlie&szlig;en"></paper-icon-button>
            <div class="title" style="text-align: center; margin-left: -15px;">Men&uuml;</div>
        </paper-toolbar>

        <paper-menu>
            <a href="https://www.gymnasium-klotzsche.de/cont/cms/front_content.php?idcat=121">
                <paper-icon-item>
                    <iron-icon icon="gavel" item-icon></iron-icon>
                    Impressum
                </paper-icon-item>
            </a>
            <a href="/raumreservierung/project/main/logout">
                <paper-icon-item>
                    <iron-icon icon="settings-power" item-icon></iron-icon>
                    Ausloggen
                </paper-icon-item>
            </a>
        </paper-menu>

    </paper-header-panel>
    <paper-scroll-header-panel main condenses condensed-header-height="58" keep-condensed-header scroll-away-topbar>

        <paper-toolbar id="header" class="medium-tall">
            <a href="https://www.gymnasium-klotzsche.de" target="_self"><img src="../../img/gykl-logo.gif" width="80px" alt="Gymnasium Klotzsche" title="Gymnasium Klotzsche"></a>
            <div class="title">$name &commat; Administration</div>
            <paper-icon-button icon="more-vert" class="bottom" style="position: absolute;right: 16px;" title="Men&uuml; &ouml;ffnen" paper-drawer-toggle></paper-icon-button>

            <paper-tabs class="bottom" style="width: calc(100% - 45px);" fit-container scrollable selected="0" autoselect>

                <paper-tab>Nutzerverwaltung</paper-tab>
                <paper-tab>Raumverwaltung</paper-tab>

            </paper-tabs>

        </paper-toolbar>

        <iron-pages selected="0">

            <admin-nutzerverwaltung></admin-nutzerverwaltung>
            <admin-raumverwaltung></admin-raumverwaltung>

        </iron-pages>

    </paper-scroll-header-panel>

</paper-drawer-panel>

<script>
    window.addEventListener('WebComponentsReady', function(){
        var pages = document.querySelector('iron-pages');
        var tabs = document.querySelector('paper-tabs');
        var routing = document.querySelector('#locator');

        selectByHash(routing, pages, tabs);
        changeUrl(tabs.selected, routing);

        if (pages.selected != tabs.selected) { pages.selected = 0; tabs.selected = 0; }

        tabs.addEventListener('iron-select', function(){
            pages.selected = this.selected;
            changeUrl(this.selected, routing);
        });
    });

    function changeUrl(selected, routing) {
        switch(selected) {
            case 0:
                routing.hash = "nutzerverwaltung";
                break;
            case 1:
                routing.hash = "raumverwaltung";
                break;
            default:
                routing.hash = "";
                break;
        }
    }

    function selectByHash(routing, pages, tabs) {
        switch(routing.hash) {
            case "nutzerverwaltung":
                pages.selected = 0;
                tabs.selected = 0;
                break;
            case "raumverwaltung":
                pages.selected = 1;
                tabs.selected = 1;
                break;
        }
    }
</script>

</body>

</html>
HTML;

    } else {
        header('Location: /raumreservierung/project/');
    }

?>
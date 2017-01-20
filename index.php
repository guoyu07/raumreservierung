<?php
    require_once ('backend/accountsystem/sessioncontroller.class.php');
    require_once ('backend/db/conf/dbconf.php');
    $sess = new SessionController($pdo);
    $sess->initialize();
?>

<!DOCTYPE html>
<html lang="de">

    <head>	
        <title>Raumreservierung</title>
		
		<!-- Webcomponent Imports -->
		<script src="bower_components/webcomponentsjs/webcomponents.min.js"></script>
		
        <!-- style imports -->
        <link rel="stylesheet" type="text/css" href="style/landingpage-index.css">
		<link rel="stylesheet" type="text/css" href="style/main.css">
		<link rel="import" href="style/main-polymer.css.html">

		<!-- Polymer Imports -->
        <!-- TODO: App Shell Sctructure :) -->
        <link rel="import" href="bower_components/paper-drawer-panel/paper-drawer-panel.html">
        <link rel="import" href="bower_components/paper-header-panel/paper-header-panel.html">
        <link rel="import" href="bower_components/paper-toolbar/paper-toolbar.html">
        <link rel="import" href="bower_components/paper-material/paper-material.html">
        <link rel="import" href="bower_components/paper-button/paper-button.html">
        <link rel="import" href="bower_components/paper-icon-button/paper-icon-button.html">
        <link rel="import" href="bower_components/iron-icons/iron-icons.html">
        <link rel="import" href="bower_components/iron-icons/social-icons.html">
        <link rel="import" href="bower_components/iron-icon/iron-icon.html">
        <link rel="import" href="bower_components/paper-spinner/paper-spinner-lite.html">
        <link rel="import" href="bower_components/paper-spinner/paper-spinner.html">
        <link rel="import" href="bower_components/iron-pages/iron-pages.html">
        <link rel="import" href="bower_components/iron-location/iron-location.html">
        <link rel="import" href="bower_components/paper-input/paper-input.html">
        <link rel="import" href="bower_components/platinum-https-redirect/platinum-https-redirect.html">
        <link rel="import" href="bower_components/platinum-sw/platinum-sw-register.html">
        <link rel="import" href="bower_components/platinum-sw/platinum-sw-cache.html">
        <link rel="import" href="bower_components/paper-dialog/paper-dialog.html">
        <link rel="import" href="bower_components/paper-dialog-scrollable/paper-dialog-scrollable.html">
        <link rel="import" href="bower_components/iron-ajax/iron-ajax.html">
        <link rel="import" href="bower_components/paper-datatable/paper-datatable.html">
        <link rel="import" href="bower_components/paper-datatable/paper-datatable-card.html">
        <link rel="import" href="bower_components/paper-menu/paper-menu.html">
        <link rel="import" href="bower_components/paper-listbox/paper-listbox.html">
        <link rel="import" href="bower_components/paper-item/paper-item.html">
        <link rel="import" href="bower_components/paper-item/paper-icon-item.html">
        <link rel="import" href="bower_components/paper-item/paper-item-body.html">
        <link rel="import" href="bower_components/paper-tabs/paper-tabs.html">
        <link rel="import" href="bower_components/paper-scroll-header-panel/paper-scroll-header-panel.html">
        <link rel="import" href="bower_components/paper-fab/paper-fab.html">
        <link rel="import" href="bower_components/paper-dropdown-menu/paper-dropdown-menu.html">
        <link rel="import" href="bower_components/paper-card/paper-card.html">

        <link rel="import" href="custom_elements/lehrer-login/lehrer-login.html">

        <!-- meta configuration -->
        <meta charset="UTF-8">
        <meta name="authors" content="Moritz Menzel, Maximilian Seiler, Benjamin Kirchhoff">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    </head>
    <body>

    <platinum-sw-register skip-waiting
                          clients-claim
                          auto-register
                          reload-on-install>
        <platinum-sw-cache default-cache-strategy="fastest"></platinum-sw-cache>
    </platinum-sw-register>


        <style is="custom-style">
            paper-button {
                --paper-button: {
                    @apply(--layout-horizontal);
                    @apply(--layout-center-center);
                    background-color: #8BC34A;
                    color: #212121;
                };
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
                    <a href="https://www.gymnasium-klotzsche.de/cont/cms/front_content.php?idcat=121">
                        <paper-icon-item>
                            <iron-icon icon="gavel" item-icon></iron-icon>
                            Impressum
                        </paper-icon-item>
                    </a>
                    <a href="/raumreservierung/project/main/errorreport">
                        <paper-icon-item>
                            <iron-icon icon="announcement" item-icon></iron-icon>
                            Einen Fehler melden
                        </paper-icon-item>
                    </a>
                </paper-menu>

            </paper-header-panel>
            <paper-header-panel main>

                <paper-material elevation="2">
                    <paper-toolbar id="header">
                        <a href="https://www.gymnasium-klotzsche.de" target="_self"><img src="img/gykl-logo.gif" width="80px" alt="Gymnasium Klotzsche" title="Gymnasium Klotzsche"></a>
                        <a href="" id="bclink"><div class="title shrink" id="breadcrumb">Raumreservierung</div></a><h3 style="font-weight: normal;" id="bcaddition"></h3>
                        <paper-icon-button icon="more-vert" title="Men&uuml; &ouml;ffnen" paper-drawer-toggle style="position: absolute; right: 12px;"></paper-icon-button>
                    </paper-toolbar>
                </paper-material>

                <br>

                <!-- Welcome Choice Content -->

                <div id="content">

                    <iron-pages selected="0" id="ironpages">

                        <paper-material elevation="1" class="autopadding">
                            <h1 class="center">Raumreservierung Gymnasium Dresden-Klotzsche</h1>
                            <br>
                            <h2 class="center">Sind Sie ein Sch&uuml;ler oder Lehrer?</h2>

                            <br>

                            <div class="contentcenter" style="width: 250px;">
                                <paper-button id="lehrer" onclick="selectPage(1)" raised>
                                    <iron-icon icon="social:school"></iron-icon>&nbsp;Lehrer
                                </paper-button>
                                <br>
                                <paper-button id="schueler" onclick="selectPage(2)" raised>
                                    <iron-icon icon="social:people"></iron-icon>&nbsp;Sch&uuml;ler
                                </paper-button>
                            </div>

                        </paper-material>
                        <lehrer-login></lehrer-login>
                        <paper-material elevation="1" class="autopadding">

                            <div class="contentcenter center">
                                <h2>Du wirst in K&uuml;rze zum Vertretungsplan weitergeleitet...</h2>
                                <paper-spinner-lite active></paper-spinner-lite>
                                <br>
                                <a href="https://www.gymnasium-klotzsche.de/vplan/view/" target="_self" style="text-decoration: underline">
                                    Falls Sie nicht weitergeleitet werden, klicken Sie hier.
                                </a>
                            </div>

                        </paper-material>

                    </iron-pages>

                </div>

            </paper-header-panel>
        </paper-drawer-panel>

    <script>

        window.addEventListener('WebComponentsReady', function(){
            autoSwitchPages();
            if(checkPage() == 2){
                redirect("https://www.gymnasium-klotzsche.de/vplan/view/");
            } else {
                document.querySelector('iron-pages').addEventListener('iron-select', function(){
                    if(this.selected == 2){
                        redirect("https://www.gymnasium-klotzsche.de/vplan/view/");
                    }
                });
            }

        });

        window.addEventListener('hashchange', function(){
            autoSwitchPages();
        });

        function autoSwitchPages(){
            if(checkPath() == "lehrer"){
                selectPage(1);
            } else if(checkPath() == "schueler"){
                selectPage(2);
            } else{
                selectPage(0);
            }
        }

        function selectPage(number){
            var ironpages = document.getElementById('ironpages');
            ironpages.select(number);
            changePath(number);
        }

        function checkPage(){
            return document.getElementById('ironpages').selected;
        }

        function changePath(number){
            var e = document.querySelector('#locator');
            var bcadd = document.getElementById('bcaddition');
            var bclink = document.getElementById('bclink');
            if(window.innerWidth <= 420){
                switch(number){
                    case 0:
                        e.hash = "";
                        bcadd.innerHTML = "";
                        break;
                    case 1:
                        e.hash = "lehrer";
                        bcadd.innerHTML = "";
                        break;
                    case 2:
                        e.hash = "schueler";
                        bcadd.innerHTML = "";
                        break;
                }
            } else {
                switch(number){
                    case 0:
                        e.hash = "";
                        bcadd.innerHTML = "";
                        break;
                    case 1:
                        e.hash = "lehrer";
                        bcadd.innerHTML = "&nbsp;>> "+e.hash;
                        break;
                    case 2:
                        e.hash = "schueler";
                        bcadd.innerHTML = "&nbsp;>> "+e.hash;
                        break;
                }
            }
        }

        function checkPath(){
            return document.querySelector('#locator').hash;
        }

        function redirect(url){
            window.location = url;
        }

    </script>

    </body>

</html>
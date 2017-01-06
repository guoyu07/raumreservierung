<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 25.12.16
     * Time: 17:58
     * FILE FOR ACCOUNT ACTIVATION
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
    else
    {
        $name = $_SESSION['name'];
        $status = $_SESSION['accstatus'];
        $acctype = $_SESSION['acctype'];
        switch($acctype)
        {
            case 1:
                $type = "Administratorkonto";
                break;
            case 2:
                $type = "Nutzerverwaltungskonto";
                break;
            case 3:
                $type = "Lehrerkonto";
                break;
            default:
                $type = "Konto";
                break;
        }

        echo <<<HTML
<!DOCTYPE html>
<html>

    <head>

        <title>Account - Aktivierung</title>

        <!-- normal imports -->
        <script src="../../bower_components/webcomponentsjs/webcomponents.min.js"></script>
        <link rel="import" href="../../additional/imports.html">
        <link rel="stylesheet" href="../../style/main.css">
        <link rel="import" href="../../style/main-polymer.css.html">

        <!-- meta configuration -->
        <meta charset="UTF-8">
        <meta name="authors" content="Moritz Menzel, Maximilian Seiler, Benjamin Kirchhoff">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">

    </head>

    <body>

        <style is="custom-style">
            paper-button{
                --paper-button: {
                    color: blue;
                }
            }
        </style>

        <platinum-https-redirect></platinum-https-redirect>
        
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
                    <a href="/raumreservierung/main/errorreport">
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
                        <a href="https://www.gymnasium-klotzsche.de" target="_self"><img src="../../img/gykl-logo.gif" width="80px" alt="Gymnasium Klotzsche" title="Gymnasium Klotzsche"></a>
                        <div class="title">Account - Aktivierung</div>
                        <paper-icon-button icon="more-vert" paper-drawer-toggle></paper-icon-button>
                    </paper-toolbar>
                </paper-material>
        
                <paper-dialog id="d1" dynamic-align with-backdrop opened no-cancel-on-esc-key no-cancel-on-outside-click>
                    <h2 id="d1head">Willkommen!</h2>
                    <paper-dialog-scrollable>
                        <p id="d1text1">
                            Sie haben sich mit dem Benutzernamen <b>$name</b> angemeldet.
                            <br>
                            Um Ihr <b>$type</b> zu aktivieren, geben Sie folgend bitte Ihre E-Mail und ein Passwort
                            f&uuml;r Ihren Account an!
                            <br><br>
                            <i>
                                (Die E-Mail - Adresse wird zur Verifizierung Ihrer Person und zur Wiederherstellung
                                Ihres Kontos bei Passwortverlust verwendet werden)
                            </i>
                        </p>
                    </paper-dialog-scrollable>
                    <div class="buttons">
                        <paper-button id="d1b1">Abbrechen</paper-button>
                        <paper-button id="d1b2">Weiter</paper-button>
                        <paper-button id="d1b3" style="display: none">Anmelden</paper-button>
                    </div>
                </paper-dialog>
                
            </paper-header-panel>
        </paper-drawer-panel>        

        <script>
            window.addEventListener('WebComponentsReady', function(){
                console.log("Webcomponents loaded");
                var d1 = document.getElementById('d1');
                d1.open();
                d1.addEventListener('iron-overlay-closed', function(){
                    d1.open();
                });
                
                var b1 = document.getElementById('d1b1');
                var b2 = document.getElementById('d1b2');
                var b3 = document.getElementById('d1b3');
                var d1head = document.getElementById('d1head');
                var d1text = document.getElementById('d1text1');
                
                b3.addEventListener('click', function(){
                    var email = document.getElementById('i1');
                    var pw = document.getElementById('i2');
                    var pw2 = document.getElementById('i2_2');
                    
                    if(email.validate() && pw.validate() && pw2.validate() && pw.value == pw2.value){
                        b1.style.display = "none";
                        b2.style.display = "none";
                        b3.style.display = "none";
                        d1head.innerHTML = "Anmeldung, bitte warten";
                        d1.center();
                        
                        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
                        xhr.open("POST", "../../backend/accountsystem/ajaxActivateAccount.php");
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
                        xhr.timeout = 10000; //Timeout after 10 Seconds (Mail might Take a while)
  
                        xhr.onloadstart = function(){
                           d1text.innerHTML = "Ihr Account wird aktiviert, bitte haben Sie einen Moment Geduld.<br><br>" +
                          "<center><paper-spinner-lite active></paper-spinner-lite></center>";
                            d1.center();
                        };
    
                        xhr.onload = function(){
                            d1text.innerHTML = "";
                            var r = JSON.parse(this.responseText);
                            if(r.success == true) {
                                d1head.innerHTML = "Aktivierung Erfolgreich!";
                                d1text.innerHTML = "Ihr Konto wurde tempor&auml;r aktiviert! Wir haben Ihnen eine Best&auml;tigungs - E-Mail zugesandt. (Die Zustellung kann einige Minuten - in seltenen F&auml;llen Stunden - dauern)" +
                                 "<br>Sollten Sie Ihre E-Mail - Adresse nicht innerhalb der n&auml;chsten 12 Stunden verifizieren, wird Ihr Konto wieder deaktiviert.<br><br>" +
                                  '<a href="/raumreservierung/project/main/logout">Zur&uuml;ck zur Startseite</a>';
                            } else {
                                d1head.innerHTML = "Aktivierung fehlgeschlagen!";
                                d1text.innerHTML = "Bei der Aktivierung Ihres Kontos ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder melden Sie Ihr Problem einem Administrator.<br>" +
                                 "Wir entschuldigen uns f&uuml;r die Unannehmlichkeiten.<br><br><b>Fehlermeldung:</b><br>"+r.message +
                                 '<br><br><a href="/raumreservierung/project/main/logout">Zur&uuml;ck zur Startseite</a>';
                                
                            }
                            d1.center();
                        };
                        
                        xhr.ontimeout = function(){
                            d1text.innerHTML = "Die Verbindung zum Server hat zu lange gedauert.<br>" +
                                "Bitte &uuml;berpr&uuml;fen Sie Ihre Internetverbindung!<br>" +
                                 "Falls Sie sich sicher sind, dass keine Probleme mit der Verbindung bestehen, &uuml;berpr&uuml;fen Sie sicherheitshalber Ihr E-Mail - Postfach.<br><br>" +
                                '<a href="/raumreservierung/project/main/logout">Zur&uuml;ck zur Startseite</a>';    //Temporary Solution TODO: Fix Mail Time
                            d1.center();
                        };
    
                        xhr.send("name=$name&pw="+pw.value+"&email="+email.value);
                        
                    }
                });    

                b2.addEventListener('click', function(){
                    b2.style.display = "none";
                    b3.style.display = "block";
                    d1head.innerHTML = "Aktivierung";
                    d1text.innerHTML = 'Geben Sie bitte Ihre E-Mail - Adresse und ihr gew&uuml;nschtes Passwort an.' +
                      '<paper-input label="E-Mail - Adresse" maxlength="48" error-message="Bitte geben Sie eine g&uuml;ltige E-Mail Adresse ein!" type="email" required auto-validate id="i1"></paper-input>' +
                      '<paper-input label="Passwort" error-message="Bitte geben Sie ein Passwort mit mindestens 8 Zeichen an!" type="password" required minlength="8" maxlength="32" auto-validate id="i2"></paper-input>' +
                      '<paper-input label="Passwort wiederholen" onkeyup="comparepw()" error-message="Bitte wiederholen Sie Ihr Passwort!" type="password" required minlength="8" maxlength="32" auto-validate id="i2_2"></paper-input>' +
                      '<br>Sollten Sie Probleme bei der Anmeldung haben, melden Sie sich bitte bei einem der Administratoren.';
                    d1.center();
                });
                    
                b1.addEventListener('click', function(){
                    d1head.innerHTML = "Bitte warten...";
                    var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
                    xhr.open("POST", "../../backend/accountsystem/ajaxLoginRequest.php");
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.timeout = 3000; //Timeout after 3 Seconds

                    xhr.onloadstart = function(){
                        d1text.innerHTML = "<paper-spinner-lite active></paper-spinner-lite>";
                        d1.center();
                    };

                    xhr.onload = function(){
                        d1text.innerHTML = "Sie werden weitergeleitet...";
                        window.location = "/raumreservierung/project/";
                        d1.center();
                    };
                    
                    xhr.ontimeout = function(){
                        d1text.innerHTML = "Die Verbindung zum Server hat zu lange gedauert.<br>" +
                            "Bitte &uuml;berpr&uuml;fen Sie Ihre Internetverbindung!";
                        d1.center();
                    };

                    xhr.send("request=logout");
                        
                });
                
            });    
            
            function comparepw()
            {
                var pw1 = document.getElementById('i2');
                var pw2 = document.getElementById('i2_2');
                if(pw1.value != pw2.value){
                    pw2.invalid = true;
                } else {
                    pw2.invalid = false;
                }
            }
            
        </script>

    </body>

</html>
HTML;


    }

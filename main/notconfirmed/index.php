<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 26.12.16
     * Time: 15:54
     */

    require_once('../../backend/accountsystem/sessioncontroller.class.php');
    require_once('../../backend/db/conf/dbconf.php');
    $sess = new SessionController($pdo);
    $sess->initialize();

    if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !isset($_SESSION['acctype']) || !isset($_SESSION['last_status_change']) || !isset($_SESSION['email']))
    {
        header('Location: /raumreservierung/project');
    } else {
        if($_SESSION['last_status_change'] != null && $_SESSION['last_status_change'] != "" && $_SESSION['accstatus'] == 2 && $_SESSION['email'] != null && $_SESSION['email'] != "")
        {

            if(isset($_GET['r'])){
                if(htmlentities($_GET['r'], ENT_QUOTES) == "logout"){
                    $sess->logout();
                    header('Location: /raumreservierung/project');
                }
            } else {
                //Main Page

                $name = $_SESSION['name'];
                $email = $_SESSION['email'];
                $last_status_change = $_SESSION['last_status_change'];

                $time = strtotime($last_status_change);         //Time in Database when logged in
                $time2 = $time+(3600*12);                       //Expiration Time (DBTime + 12h)
                $now = time();                                  //Time at Page-Load (Actual Time)

                $check = $time2 > $now;                         //Check if Expiration Time exceeds Actual Time
                                                                //1 = Still Active; 0 = Expired

                $remainingtime = $time2 - 3600 - $now;
                $h = date('H', $remainingtime);
                $m = date('i', $remainingtime);

                $timeremainingformatted = "$h Stunde(n) und $m Minute(n)";
                $extimeformatted = date('d.m.Y', $time2) . " um " . date('H:i', $time2);

                if($check === true){
                    $msg = <<<HTML
                <h2 style="text-align: center;">Herzlichen Gl&uuml;ckwunsch!</h2>
                    <p>
                        Hallo, <b>$name</b>!<br>
                        Sie haben sich erfolgreich f&uuml;r die Raumreservierung angemeldet.<br><br>
                        Um die Einrichtung vollst&auml;ndig abzuschlie&szlig;en, bitten wir Sie, Ihre E-Mail - Adresse
                        innerhalb der n&auml;chsten <br><b>$timeremainingformatted</b> zu best&auml;tigen.<br><br>
                        <i>(Wenn Sie Ihre E-Mail bis zum <b>$extimeformatted</b> nicht best&auml;tigt haben, wird Ihr Konto wieder in
                        den Ausgangszustand zur&uuml;ckesetzt, d.h. Ihr Passwort und Ihre E-Mail werden aus der Datenbank
                        gel&ouml;scht und auf die Standardwerte gesetzt.)</i>
                    </p>
                    <br>
                    <hr>
                    <br>
                    <p>
                        Da Sie Ihre E-Mail noch nicht best&auml;tigt haben, haben Sie noch keinen Zugriff auf die Nutzung
                        der Raumreservierung. Sollten Sie Fragen oder Probleme bei und zu der Best&auml;tigung Ihrer E-Mail - Adresse 
                        haben, melden Sie sich bitte bei einem der Administratoren f&uuml;r die Raumreservierung.<br><br>
                        Vielen Dank f&uuml;r Ihr Verst&auml;ndnis,<br>
                        Ihr Team der Raumreservierung!<br>
                    </p>
HTML;
                } else {
                    $msg = <<<HTML
                <h2 style="text-align: center;">Ihre Aktivierungsfrist ist abgelaufen!</h2>
                <p>
                    Hallo, <b>$name</b>!<br>
                    Leider haben Sie Ihr Konto nicht innerhalb der vorgegebenen 12h nach der Anmeldung per E-Mail best&auml;tigt.<br>
                    Aus diesen Gr&uuml;nden wurde Ihr Konto wieder auf den Ausgangszustand zur&uuml;ckgesetzt. Um Ihr Konto erneut zu
                    aktivieren, melden Sie sich einfach ab (Men&uuml; oben rechts) und anschlie&szlig;end neu an. Sie werden erneut aufgefordert,
                    ein neues Passwort und Ihre E-Mail einzugeben.<br><br>
                    Sollten Sie Probleme mit der Best&auml;tigung Ihrer E-Mail - Adresse gehabt haben, melden Sie sich bitte bei einem der
                    Administratoren f&uuml;r die Raumreservierung.<br><br>
                    Die Deaktivierung Ihres Kontos ist eine reine Sicherheitsma&szlig;nahme.<br><br>
                    Vielen Dank f&uuml;r Ihr Verst&auml;ndnis,<br>Ihr Team der Raumreservierung!<br>
                </p>
HTML;
                }

                echo <<<HTML
            
<!DOCTYPE html>
<html>

    <head>

        <title>E-Mail - Best&auml;tigung ausstehend</title>

        <!-- normal imports -->
        <script src="../../bower_components/webcomponentsjs/webcomponents.min.js"></script>
        <link rel="import" href="../../additional/imports.html">
        <link rel="stylesheet" href="../../style/main.css">
        <link rel="import" href="../../style/main-polymer.css.html">

        <!-- meta configuration -->
        <meta charset="UTF-8">
        <meta name="authors" content="Moritz Menzel, Maximilian Seiler, Benjamin Kirchhoff">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
        
        <style is="custom-style">
            p{ color: #212121; }
            #textcontent{
                max-width: 800px;
                width: 90%;
                margin: 0 auto;
                text-align: justify;
            }
            #content{ padding: 10px 10px; }
            #textmaterial{ padding-bottom: 5px; }
            paper-button{
                -webkit-transition: background-color 0.5s, color 0.5s;-moz-transition: background-color 0.5s, color 0.5s;-ms-transition: background-color 0.5s, color 0.5s;-o-transition: background-color 0.5s, color 0.5s;transition: background-color 0.5s, color 0.5s;
            }
            paper-button:hover{
                background-color: #607D8B;
                color: #eee;
            }
            #lob{
                width: 90%;
                margin: 0 auto;
            }
            hr{
                border: 1px dotted #212121;
                border-top: none;
                max-width: 420px;
                margin: 0 auto;
            }
            a, a:active{ color: black; text-decoration: none; }
        </style>
        
    </head>
    
    <body>
    
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
                    <br>
                    <a href="index.php?r=logout">
                        <paper-item>
                            <paper-button id="lob" name="logout"><iron-icon icon="exit-to-app"></iron-icon>Abmelden</paper-button>
                        </paper-item>    
                    </a>
                </paper-menu>

            </paper-header-panel>
            <paper-header-panel main>
    
                <paper-material elevation="2">
                    <paper-toolbar id="header">
                        <a href="https://www.gymnasium-klotzsche.de" target="_self"><img src="../../img/gykl-logo.gif" width="80px" alt="Gymnasium Klotzsche" title="Gymnasium Klotzsche"></a>
                        <div class="title">E-Mail - Best&auml;tigung ausstehend</div>
                        <paper-icon-button paper-drawer-toggle icon="more-vert" style="position: absolute; right: 12px;"></paper-icon-button>
                    </paper-toolbar>
                </paper-material>
                
                <div id="content">
                    
                    <paper-material elevation="1" id="textmaterial">
                    
                        <div id="textcontent">
                            $msg
                        </div>
                   
                
                    </paper-material>
                
                </div>
                
            </paper-header-panel>    
        </paper-drawer-panel>
    </body>

</html>

HTML;

            }
        } else {
            header('Location: /raumreservierung/project');
        }
    }

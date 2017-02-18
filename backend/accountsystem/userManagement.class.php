<?php

    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 23.12.16
     * Time: 19:54
     */

    class userManagement
    {

        public function __construct($pdo)
        {

            // Definition of Link-URLs in E-Mails
            $this->url = "https://gykl-rr.lima.zone";

            if(is_a($pdo, 'PDO')){
                $this->pdo = $pdo;
            } else {
                $this->pdo = false;
            }
        }

        private function hash_password($password, $iterations=1024*24){
            $salt = bin2hex(openssl_random_pseudo_bytes(64));
            //Password hashing
            $passhash = hash_pbkdf2("sha512", $password, $salt, $iterations, 255);

            return array("password" => $passhash, "salt" => $salt, "iterations" => $iterations);
        }

        public function addUser($name, $password, $type = 3, $status = 1){

            if($this->pdo){

                $iterations = 1024*24;
                $hash = $this->hash_password($password, $iterations);
                $pw = $hash['password'];
                $salt = $hash['salt'];

                $sql1 = "INSERT INTO accounts_users(name, password, salt, iterations) VALUES (:accname, :pw, :salt, :iterations)";
                $sql2 = "INSERT INTO accounts(name, type, status) VALUES (:accname, :acctype, :accstatus)";

                $r1 = $this->pdo->prepare($sql1);
                $r2 = $this->pdo->prepare($sql2);

                try {
                    $this->pdo->beginTransaction();
                    $r1->execute(array(":accname" => $name, ":pw" => $pw, ":salt" => $salt, ":iterations" => $iterations));
                    $r2->execute(array(":accname" => $name, ":acctype" => $type, ":accstatus" => $status));
                    $this->pdo->commit();
                    return array("error" => false, "message" => "Der Nutzer $name (Account-Typ: $type; Account-Status: $status) wurde erfolgreich erstellt!");

                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    return array("error" => true, "message" => $e->getMessage());
                }



            } else { return array("error" => true, "message" => "PDO konnte nicht initialisiert werden!"); }

        }

        public function activateUser($name, $pw, $email) {
            // The Variable Contents have to be checked before calling the function!
            // First of all, checking if the email is not already in the database!

            if(!$this->isEmailInDB($email)) {
                $newStatus = 2;     //Temporary; waiting for E-Mail - Confirmation

                $newPass = $this->hash_password($pw);
                $npw = $newPass['password'];
                $salt = $newPass['salt'];
                $iterations = $newPass['iterations'];

                $sql1 = "UPDATE accounts SET status=:newstatus, last_status_change=NOW(), activationcode=:activationcode WHERE name=:accname";
                $r1 = $this->pdo->prepare($sql1);

                $activationcode = bin2hex(openssl_random_pseudo_bytes(32));

                $sql2 = "UPDATE accounts_users SET email=:email, password=:pw, salt=:salt, iterations=:iterations WHERE name=:accname";
                $r2 = $this->pdo->prepare($sql2);

                try {
                    $this->pdo->beginTransaction();
                    $r1->execute(array(":newstatus" => $newStatus, ":accname" => $name, ":activationcode" => $activationcode));
                    $r2->execute(array(":email" => $email, ":pw" => $npw, ":salt" => $salt, ":iterations" => $iterations, ":accname" => $name));

                    $mail = $this->sendConfirmationMail($activationcode, $name, $email);

                    if($mail === true){
                        $this->pdo->commit();
                        return array("error" => false);
                    } else {
                        $this->pdo->rollBack();
                        return array("error" => true, "message" => "Fehler: Die Bestätigungs-Mail konnte aufgrund eines Fehlers nicht gesendet werden!");
                    }
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    return array("error" => true, "message" => "Es ist ein Fehler aufgetreten: ".$e->getMessage());
                }
            } else {
                return array("error" => true, "message" => "Diese E-Mail ist bereits mit einem anderen Konto verknüpft. Bitte kontaktieren Sie unverzüglich einen Administrator!");
            }
        }

        public function isEmailInDB($email) {
            $sql = "SELECT email FROM accounts_users WHERE email=:email";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":email" => $email));
            return !empty($r->fetchAll());
        }

        public function isUserInDB($name) {
            $sql = "SELECT accounts.name FROM accounts WHERE accounts.name=:accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            return !empty($r->fetchAll());
        }

        private function sendConfirmationMail($activationcode, $name, $email)
        {

            $fullname = $this->getFullName($name);
            $prename = (is_array($fullname)) ? $fullname['prename'] : $name;
            $surname = (is_array($fullname)) ? $fullname['surname'] : "";

            $to         = $email;
            $subject    = "Gykl Raumreservierung - Anmeldung";

            $headers    = "MIME-Version: 1.0" . "\r\n";
            $headers   .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $headers   .= "To: $prename $surname <$email>" . "\r\n";
            $headers   .= "From: activation@lima.zone" . "\r\n";
            $headers   .= "Reply-To: activation@lima.zone" . "\r\n";
            $headers   .= "X-Mailer: PHP/" . phpversion() . "\r\n";

            // Definition of Website Host
            $url = $this->url;

            $message    = <<<HTML
<!DOCTYPE html><html style="font-family: Roboto, Noto, sans-serif;color: #212121;margin: 0 auto;min-width: 319px;"><head> <title>Account - Aktivierung</title> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes"> <style>html, body{font-family: Roboto, Noto, sans-serif; color: #212121; margin: 0 auto; min-width: 319px;}#content{width: 90%; max-width: 600px; height: auto; margin: 0 auto 58px auto; text-align: left;}#footer{margin: 0 auto; width: 100%; height: 48px; background-color: #212121; color: white; text-align: center;}#footText{font-size: 13px; padding-top: 8px;}#pseudoButton{background-color: #2196F3; height: 48px; width: auto; text-align: center; font-size: 22px; color: white; -webkit-transition: background-color 0.2s; -moz-transition: background-color 0.2s; -ms-transition: background-color 0.2s; -o-transition: background-color 0.2s; transition: background-color 0.2s; border-radius: 5px; margin: 0 auto;}#pseudoButton:hover{background-color: #64B5F6; cursor: pointer;}a{color: #33691E; text-decoration: none; -webkit-transition: color 0.2s; -moz-transition: color 0.2s; -ms-transition: color 0.2s; -o-transition: color 0.2s; transition: color 0.2s;}a:hover{color: #558B2F;}#buttonLink{color: white; text-decoration: none;}hr{width: 70%; border: none; border-bottom: 1px solid #E0E0E0; margin: 20px auto;}#disclaimer{font-size: 14px; color: #616161;}/** Fix for apple clients */ @media only screen and (min-device-width: 601px){#content{width: 600px !important;}}</style></head><body style="font-family: Roboto, Noto, sans-serif;color: #212121;margin: 0 auto;min-width: 319px;"><!--[if (get mso 9)|(IE)]><div id="content" style="width: 600px;height: auto; margin: 96px auto 58px auto;text-align: left;"><![endif]--><div id="content" style="width: 90%;max-width: 600px;height: auto;margin: 0 auto 58px auto;text-align: left;"> <h1 style="text-align: center;">Hallo, $prename $surname!</h1> <p style="font-size: 16px;"> <br><b>Sie haben es fast geschafft!</b><br><br>Um Ihre E-Mail '$email' zu best&auml;tigen, &ouml;ffnen Sie bitte den untenstehenden Link.<br>Anschlie&szlig;end werden Sie sich mit Ihrem Konto bei der Raumreservierung einloggen k&ouml;nnen. <br><br></p><a href="$url?name=$name&code=$activationcode#confirm-email" title="$url#confirm-email" id="buttonLink" target="_blank" style="color: white;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;"> <div id="pseudoButton" style="background-color: #2196F3;height: 48px;width: auto;text-align: center;font-size: 22px;color: white;-webkit-transition: background-color 0.2s;-moz-transition: background-color 0.2s;-ms-transition: background-color 0.2s;-o-transition: background-color 0.2s;transition: background-color 0.2s;border-radius: 5px;margin: 0 auto;"> <p style="padding-top: 10px;">$name aktivieren!</p></div></a> <hr style="width: 70%;border: none;border-bottom: 1px solid #E0E0E0;margin: 20px auto;"> <p id="disclaimer" style="font-size: 13px;color: #616161;"> <b>Information</b><br>Diese E-Mail wurde im Rahmen der Account-Aktivierung f&uuml;r die Raumreservierung des <a href="https://gymnasium-klotzsche.de" title="www.gymnasium-klotzsche.de" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Gymnasiums Dresden-Klotzsche</a> versandt.<br>Wenn Sie diese E-Mail nicht angefordert haben, dann ignorieren Sie sie einfach.<br><br>Sollten Sie weitere Fragen oder Probleme haben, k&ouml;nnen Sie sich direkt an das <a href="https://gymnasium-klotzsche.de" title="www.gymnasium-klotzsche.de" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Gymnasium Dresden-Klotzsche</a> oder die <a href="$url#imprint" title="$url#imprint" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Raumreservierung</a> wenden. <br><br>Wir entschuldigen uns f&uuml;r jedwede Art von Unannehmlichkeiten.<br>Ihr Team der Raumreservierung des Gymnasiums Dresden Klotzsche :-) </p></div><div id="footer" style="margin: 0 auto;width: 100%;height: 48px;background-color: #212121;color: white;text-align: center;"> <p id="footText" style="font-size: 12px;padding-top: 9px;"> Raumreservierung &copy; 2017 by<br>Moritz Menzel, Benjamin Kirchhoff, Maximilian Seiler</p></div></body></html>
HTML;
            mail($to, $subject, $message, $headers);
            return true;    //TODO: Change to 'return mail()' again but Webserver Times @ LimaCity are too disgusting
        }

        public function confirmUser($name, $code){

            $sql1 = "SELECT status, email_confirmed, activationcode, email, last_status_change FROM accounts, accounts_users
                     WHERE accounts_users.name = accounts.name AND accounts.name = :accname";
            $r1 = $this->pdo->prepare($sql1);

            try {
                $this->pdo->beginTransaction();
                $r1->execute(array(":accname" => $name));
                $this->pdo->commit();

                $res = $r1->fetchAll();
                if(!empty($res)){

                    $res = $res[0];

                    if($res['status'] == 2 && $res['email_confirmed'] == 0 && $res['email'] != null && $res['activationcode'] != null){

                        if($res['last_status_changed'] != null) {
                            if((strtotime($res['last_status_change']) + (3600*12)) < time()) {
                                // 12h are over
                                $this->resetAccount($name);
                                return array("error" => true, "message" => "Die Aktivierungsfrist von 12 Stunden ist abgelaufen! Ihr Konto wurde wieder deaktiviert!");
                            } else {
                                if(hash_equals($res['activationcode'], $code)){
                                    //Confirm User

                                    $sql2 = "UPDATE accounts SET status=:accstatus, last_status_change=NOW(), email_confirmed=1, activationcode=NULL, last_email=NULL
                                     WHERE name=:accname";
                                    $r2 = $this->pdo->prepare($sql2);

                                    try {
                                        $this->pdo->beginTransaction();
                                        $r2->execute(array(":accstatus" => 3, ":accname" => $name));
                                        $this->pdo->commit();
                                        return (array("error" => false, "message" => "Ihr Account wurde erfolgreich aktiviert!"));
                                    } catch (Exception $e) {
                                        $this->pdo->rollBack();
                                        return (array("error" => true, "message" => "Fehler: ".$e->getMessage()));
                                    }

                                } else {
                                    return array("error" => true, "message" => "Fehler: Der Bestätigungscode ist nicht korrekt!");
                                }
                            }
                        } else {
                            if(hash_equals($res['activationcode'], $code)){
                                //Confirm User

                                $sql2 = "UPDATE accounts SET status=:accstatus, last_status_change=NOW(), email_confirmed=1, activationcode=NULL
                                     WHERE name=:accname";
                                $r2 = $this->pdo->prepare($sql2);

                                try {
                                    $this->pdo->beginTransaction();
                                    $r2->execute(array(":accstatus" => 3, ":accname" => $name));
                                    $this->pdo->commit();
                                    return (array("error" => false, "message" => "Ihr Account wurde erfolgreich aktiviert!"));
                                } catch (Exception $e) {
                                    $this->pdo->rollBack();
                                    return (array("error" => true, "message" => "Fehler: ".$e->getMessage()));
                                }

                            } else {
                                return array("error" => true, "message" => "Fehler: Der Bestätigungscode ist nicht korrekt!");
                            }
                        }
                    } elseif($res['status'] == 3) {
                        return array("error" => true, "message" => "Der Account ($name) wurde bereits aktiviert!");
                    } elseif($res['status'] == 1) {
                        return array("error" => true, "message" => "Der Account ($name) ist momentan deaktiviert!");
                    } else {
                        return array("Es sind nicht alle benötigten Daten in die Datenbank eingetragen! Bitte melden Sie diesen Fehler unbedingt einem Administrator!");
                    }

                } else { return array("error" => true, "message" => "Fehler: Der Nutzername wurde nicht in der Datenbank gefunden!"); }

            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Fehler: ".$e->getMessage());
            }

        }

        public function getAllUsers() {
            $sql = "SELECT accounts.A_ID, accounts.name, accounts.type, accounts.status, coalesce(accounts.last_login, '-') as last_login, accounts.email_confirmed, coalesce(accounts_users.email,'-') as email
                    FROM accounts, accounts_users
                    WHERE accounts.name = accounts_users.name
                    ORDER BY A_ID";
            $r = $this->pdo->prepare($sql);
            try {
                $r->execute();
                return array("error" => false, "response" => $r->fetchAll());
            } catch (PDOException $e) {
                return array("error" => true, "message" => $e->getMessage());
            }
        }

        public function updateUserData($oldname, $name, $type, $status){

            $sql = "UPDATE accounts SET type=:type, status=:status
                    WHERE accounts.name = :oldname";
            $sql2 = "UPDATE accounts_users SET name=:newname WHERE accounts_users.name = :oldname";
            $r = $this->pdo->prepare($sql);
            $r2 = $this->pdo->prepare($sql2);

            try {
                $this->pdo->beginTransaction();
                $r->execute(array(":type" => $type, ":status" => $status, ":oldname" => $oldname));
                $r2->execute(array(":newname" => $name, ":oldname" => $oldname));
                $this->pdo->commit();

                $newData = $this->getAllUsers();
                if(!$newData['error']){
                    return array("error" => false, "response" => $newData['response']);
                } else {
                    return array("error" => true, "message" => "Fehler bei der Datenbankanfrage: ".$newData['message']);
                }
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Fehler beim Ändern der Nutzerdaten: ".$e->getMessage());
            }

        }

        public function deleteUser($name){

            $sql1 = "DELETE FROM accounts_users WHERE name=:username";
            $sql2 = "DELETE FROM accounts WHERE name=:username";

            $r1 = $this->pdo->prepare($sql1);
            $r2 = $this->pdo->prepare($sql2);

            try {
                $this->pdo->beginTransaction();
                $r1->execute(array(":username" => $name));
                $r2->execute(array(":username" => $name));
                $this->pdo->commit();

                $newData = $this->getAllUsers();

                if($newData['error'] == false) {

                    if(isset($newData['response']) && !empty($newData['response'])) {

                        $res = $newData['response'];
                        return array("error" => false, "data" => $res);

                    } else {
                        return array("error" => true, "message" => "Es sind keine Daten in der Datenbank vorhanden.");
                    }

                } else {
                    return array("error" => true, "message" => "Fehler beim Abruf der Daten: ".$newData['message']);
                }

            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Fehler bei der Datenbank-Abfrage: ".$e->getMessage());
            }

        }

        public function getErrors(){

            // The LIMIT = 128 is purely case-sensitive, there should never be that much error - reports...
            // and if someone thinks he has to spam, we have to manually clean the table
            $sql = "SELECT errorID, name, email, coalesce(page, '-') as page, text, created FROM errorreport ORDER BY created DESC LIMIT 128";
            $res = $this->pdo->query($sql)->fetchAll();

            for($i=0;$i < count($res);$i++){
                $res[$i]['text'] = preg_replace("/::AMP::/", "&", $res[$i]['text']);

                // Following two lines will prevent HTML injections but allow custom regex'ed newlines to be parsed as
                // Normal line breaks; otherwise it will be very hard to read ;)
                $res[$i]['text'] = htmlentities($res[$i]['text'], ENT_QUOTES);
                $res[$i]['text'] = preg_replace("/::NEWLINE::/", '<br>', $res[$i]['text']);

                $res[$i]['created'] = date("d.m.Y - H:i", strtotime($res[$i]['created']));
            }

            return array("error" => false, "data" => $res);
        }

        public function deleteError($id) {
            $sql = "DELETE FROM errorreport WHERE errorID=:errorid";
            $r = $this->pdo->prepare($sql);

            try {
                $this->pdo->beginTransaction();
                $r->execute(array(":errorid" => $id));
                $this->pdo->commit();

                return $this->getErrors();
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Es ist ein Fehler beim Löschen aufgetreten:<br>".$e->getMessage());
            }
        }

        public function changePassword($name, $pw) {
            $sql = "UPDATE accounts_users SET password=:pw, salt=:salt, iterations=:iterations WHERE accounts_users.name=:name";
            $a = $this->hash_password($pw);
            $pw = $a['password'];
            $salt = $a['salt'];
            $iterations = $a['iterations'];

            $r = $this->pdo->prepare($sql);

            try{
                $this->pdo->beginTransaction();
                $stat = $r->execute(array(":pw" => $pw, ":salt" => $salt, ":iterations" => $iterations, ":name" => $name));
                $this->pdo->commit();
                return array("error" => false);
            } catch( PDOException $e ) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Es ist ein Fehler beim Ändern des Passwortes aufgetreten: ".$e->getMessage());
            }
        }

        public function resetAccount($name) {
            $sql1 = "UPDATE accounts SET status=1, last_status_change=NULL, email_confirmed=0, activationcode=NULL 
                     WHERE accounts.name = :accname";
            $sql2 = "UPDATE accounts_users SET password=:pw, salt=:salt, iterations=:iterations, email=NULL
                     WHERE accounts_users.name = :accname";
            $r1 = $this->pdo->prepare($sql1);
            $r2 = $this->pdo->prepare($sql2);

            $usql = "SELECT name, lehrer_accname, lehrer_vorname, lehrer_nachname FROM accounts_users, plan_lehrer
                     WHERE plan_lehrer.lehrer_accname = accounts_users.name AND accounts_users.name = :accname";
            $u = $this->pdo->prepare($usql);

            try {

                $this->pdo->beginTransaction();
                $u->execute(array(":accname" => $name));
                $this->pdo->commit();
                $user = $u->fetchAll();
                if(!empty($user)) {
                    $user = $user[0];

                    // Reset Account by Teacher name
                    $pwhash = $this->hash_password("gykl@".strtolower($user['lehrer_vorname']));
                    $pw = $pwhash['password'];
                    $salt = $pwhash['salt'];
                    $iterations = $pwhash['iterations'];

                    try {
                        $this->pdo->beginTransaction();
                        $r1->execute(array(":accname" => $name));
                        $r2->execute(array(":accname" => $name, ":pw" => $pw, ":salt" => $salt, ":iterations" => $iterations));
                        $this->pdo->commit();
                        return array("error" => false);
                    } catch (PDOException $e){
                        $this->pdo->rollBack();
                        return array("error" => true, "message" => $e->getMessage());
                    }

                } else {
                    // Reset Account with standard PW
                    $pwhash = $this->hash_password("gykl@2016");
                    $pw = $pwhash['password'];
                    $salt = $pwhash['salt'];
                    $iterations = $pwhash['iterations'];

                    try {
                        $this->pdo->beginTransaction();
                        $r1->execute(array(":accname" => $name));
                        $r2->execute(array(":accname" => $name, ":pw" => $pw, ":salt" => $salt, ":iterations" => $iterations));
                        $this->pdo->commit();
                        return array("error" => false);
                    } catch (PDOException $e) {
                        $this->pdo->rollBack();
                        return array("error" => true, "message" => $e->getMessage());
                    }

                }


            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => $e->getMessage());
            }
        }

        public function getPrename($name) {

            $sql = "SELECT name, lehrer_accname, lehrer_vorname FROM accounts_users, plan_lehrer
                     WHERE plan_lehrer.lehrer_accname = accounts_users.name AND accounts_users.name = :accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            $res = $r->fetchAll();
            if(!empty($res)) {
                $u = $res[0];
                return $u['lehrer_vorname'];
            } else {
                return $name;
            }

        }

        public function getFullName($name) {
            $sql = "SELECT name, lehrer_accname, lehrer_vorname, lehrer_nachname, lehrer_kurz FROM accounts_users, plan_lehrer
                     WHERE plan_lehrer.lehrer_accname = accounts_users.name AND accounts_users.name = :accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            $res = $r->fetchAll();
            if(!empty($res)) {
                $u = $res[0];
                return array("prename" => $u['lehrer_vorname'], "surname" => $u['lehrer_nachname'], "lehrer_kurz" => $u['lehrer_kurz']);
            } else {
                return $name;
            }
        }

        public function sendPasswordResetMail($name, $email) {

            if($this->isEmailInDB($email)) {

                if($this->isUserInDB($name)) {

                    if($this->isUserActivated($name)) {

                        if($this->lastEmailValidDate($name)) {

                            $fullname = $this->getFullName($name);
                            $prename = (is_array($fullname)) ? $fullname['prename'] : $name;
                            $surname = (is_array($fullname)) ? $fullname['surname'] : "";

                            $code = bin2hex(openssl_random_pseudo_bytes(32));

                            $to         = $email;
                            $subject    = "Gykl Raumreservierung - Zurücksetzung Ihres Passwortes";

                            $headers    = "MIME-Version: 1.0" . "\r\n";
                            $headers   .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                            $headers   .= "To: $prename $surname <$email>" . "\r\n";
                            $headers   .= "From: activation@lima.zone" . "\r\n";
                            $headers   .= "Reply-To: activation@lima.zone" . "\r\n";
                            $headers   .= "X-Mailer: PHP/" . phpversion() . "\r\n";

                            // Definition of Website Host
                            $url = $this->url;

                            $message    = <<<HTML
<!DOCTYPE html><html style="font-family: Roboto, Noto, sans-serif;color: #212121;margin: 0 auto;min-width: 319px;"><head> <title>Passwort - Wiederherstellung</title> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes"> <style>html, body{font-family: Roboto, Noto, sans-serif; color: #212121; margin: 0 auto; min-width: 319px;}#content{width: 90%; max-width: 600px; height: auto; margin: 0 auto 58px auto; text-align: left;}#footer{margin: 0 auto; width: 100%; height: 48px; background-color: #212121; color: white; text-align: center;}#footText{font-size: 13px; padding-top: 8px;}#pseudoButton{background-color: #2196F3; height: 48px; width: auto; text-align: center; font-size: 22px; color: white; -webkit-transition: background-color 0.2s; -moz-transition: background-color 0.2s; -ms-transition: background-color 0.2s; -o-transition: background-color 0.2s; transition: background-color 0.2s; border-radius: 5px; margin: 0 auto;}#pseudoButton:hover{background-color: #64B5F6; cursor: pointer;}a{color: #33691E; text-decoration: none; -webkit-transition: color 0.2s; -moz-transition: color 0.2s; -ms-transition: color 0.2s; -o-transition: color 0.2s; transition: color 0.2s;}a:hover{color: #558B2F;}#buttonLink{color: white; text-decoration: none;}hr{width: 70%; border: none; border-bottom: 1px solid #E0E0E0; margin: 20px auto;}#disclaimer{font-size: 14px; color: #616161;}/** Fix for apple clients */ @media only screen and (min-device-width: 601px){#content{width: 600px !important;}}</style></head><body style="font-family: Roboto, Noto, sans-serif;color: #212121;margin: 0 auto;min-width: 319px;"><!--[if (get mso 9)|(IE)]><div id="content" style="width: 600px;height: auto; margin: 96px auto 58px auto;text-align: left;"><![endif]--><div id="content" style="width: 90%;max-width: 600px;height: auto;margin: 0 auto 58px auto;text-align: left;"> <h1 style="text-align: center;">Hallo, $prename $surname!</h1> <p style="font-size: 16px;"> <br>Wir haben eine Anfrage zur Wiederherstellung Ihres Passwortes erhalten. Um sicherzustellen, dass diese Anfrage von Ihnen stammt, m&uuml;ssen Sie die &Auml;nderung best&auml;tigen. Klicken Sie dazu auf den untenstehenden Link. </p><hr style="width: 70%;border: none;border-bottom: 1px solid #E0E0E0;margin: 20px auto;"> <a href="$url?name=$name&code=$code#reset-password" title="$url" id="buttonLink" target="_blank" style="color: white;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;"> <div id="pseudoButton" style="background-color: #EF5350;height: 48px;width: auto;text-align: center;font-size: 22px;color: white;-webkit-transition: background-color 0.2s;-moz-transition: background-color 0.2s;-ms-transition: background-color 0.2s;-o-transition: background-color 0.2s;transition: background-color 0.2s;border-radius: 5px;margin: 0 auto;"> <p style="padding-top: 10px;">Passwort zur&uuml;cksetzen!</p></div></a> <hr style="width: 70%;border: none;border-bottom: 1px solid #E0E0E0;margin: 20px auto;"> <p style="font-size: 14px;"> <i> Sollten Sie diese E-Mail nicht angefordert haben, versucht m&ouml;glicherweise ein Dritter, Ihr Passwort zu &auml;ndern.<br>Wenn Sie sich noch an Ihr eigenes Accountpasswort erinnern, klicken Sie bitte <b>nicht</b> auf den Link und l&ouml;schen diese E-Mail am besten einfach wieder! </i> </p><hr style="width: 70%;border: none;border-bottom: 1px solid #E0E0E0;margin: 20px auto;"> <p id="disclaimer" style="font-size: 13px;color: #616161;"> <b>Information</b><br>Diese E-Mail wurde im Rahmen der Passwortwiederherstellung der Raumreservierung des <a href="https://gymnasium-klotzsche.de" title="www.gymnasium-klotzsche.de" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Gymnasiums Dresden-Klotzsche</a> versandt.<br>Wenn Sie diese E-Mail nicht angefordert haben, dann ignorieren Sie sie einfach.<br><br>Sollten Sie weitere Fragen oder Probleme haben, k&ouml;nnen Sie sich direkt an das <a href="https://gymnasium-klotzsche.de" title="www.gymnasium-klotzsche.de" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Gymnasium Dresden-Klotzsche</a> oder die <a href="$url#imprint" title="$url#imprint" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Raumreservierung</a> wenden. <br><br>Wir entschuldigen uns f&uuml;r jedwede Art von Unannehmlichkeiten.<br>Ihr Team der Raumreservierung des Gymnasiums Dresden Klotzsche :-) </p></div><div id="footer" style="margin: 0 auto;width: 100%;height: 48px;background-color: #212121;color: white;text-align: center;"> <p id="footText" style="font-size: 12px;padding-top: 9px;"> Raumreservierung &copy; 2017 by<br>Moritz Menzel, Benjamin Kirchhoff, Maximilian Seiler </p></div></body></html>
HTML;

                            $sql = "UPDATE accounts SET last_email=NOW(), activationcode=:activationcode WHERE accounts.name=:accname";
                            $r = $this->pdo->prepare($sql);

                            try {
                                $this->pdo->beginTransaction();
                                $stat = $r->execute(array(":accname" => $name, ":activationcode" => $code));
                                $this->pdo->commit();
                                if($stat) {
                                    mail($to, $subject, $message, $headers);
                                    return array("message" => "Es wurde eine Bestätigungsmail an Ihre E-Mail - Adresse gesendet!");
                                } else {
                                    return array("message" => "Es konnten keine Daten in die Datenbank eingetragen werden. Bitte melden Sie diesen Fehler einem Administrator!");
                                }
                            } catch (PDOException $e) {
                                $this->pdo->rollBack();
                                return array("message" => $e->getMessage());
                            }

                        } else {
                            return array("message" => "Sie haben erst vor Kurzem Ihr Passwort zurückgesetzt. Sie können nur maximal alle 24h eine Wiederherstellung beantragen!");
                        }

                    } else {
                        return array("message" => "Der Account muss aktiviert sein, damit sein Passwort zurückgesetzt werden kann!");
                    }

                } else {
                    return array("message" => "Die eingegebenen Daten sind ungültig!");
                }

            } else {
                return array("message" => "Die eingegebenen Daten sind ungültig!");
            }

        }

        private function lastEmailValidDate($name){
            $sql = "SELECT last_email FROM accounts WHERE accounts.name = :accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            $res = $r->fetchAll();
            $erg = $res[0];

            return ((strtotime($erg['last_email']) + (3600*24)) < time());
        }

        public function resetPassword($name, $pw) {
            // Final reset called when activation code is correct
            $sql = "UPDATE accounts SET activationcode=NULL WHERE name=:accname";
            $r = $this->pdo->prepare($sql);
            try {
                $this->pdo->beginTransaction();
                $r->execute(array(":accname" => $name));
                $this->pdo->commit();
                $status = $this->changePassword($name, $pw);
                if($status['error'] == true) {
                    return array("error" => true, "message" => $status['message']);
                } else {
                    return array("error" => false);
                }
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Es ist ein Fehler bei der Datenbankverbindung aufgetreten: ".$e->getMessage());
            }
        }

        public function validateQuery($name, $code) {
            $sql = "SELECT name, activationcode FROM accounts WHERE name=:accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            $res = $r->fetchAll();
            if(!empty($res)) {
                $data = $res[0];
                if($data['activationcode'] != null) {
                    if(hash_equals($data['activationcode'], $code)) {
                        return array("error" => false);
                    } else {
                        return array("error" => true, "message" => "Die übergebenen Werte sind ungültig!");
                    }
                } else {
                    return array("error" => true, "message" => "Sie haben keine Passwortwiederherstellung angefordert oder Ihr Account wurde währenddessen von einem Administrator zurückgesetzt!");
                }
            } else {
                return array("error" => true, "message" => "Die übergebenen Werte sind ungültig!");
            }
        }

        function generatePassword($length, $keyspace='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
            $keyspace = str_split($keyspace);
            $max = count($keyspace) - 1;

            $str = '';
            for($i = 0; $i < $length; $i++) {
                $c = ord(openssl_random_pseudo_bytes(1));
                while ($c > $max) {
                    $c = ord(openssl_random_pseudo_bytes(1));
                }
                $str .= $keyspace[$c];
            }
            return $str;
        }

        private function sendPasswordChangeMail($name, $email) {
            $fullname = $this->getFullName($name);
            $prename = (is_array($fullname)) ? $fullname['prename'] : $name;
            $surname = (is_array($fullname)) ? $fullname['surname'] : "";

            $timestamp = time();
            $date = date('d.m.Y', $timestamp);
            $time = date('H:i', $timestamp);

            $to         = $email;
            $subject    = "Gykl Raumreservierung - Änderung Ihres Passwortes";

            $headers    = "MIME-Version: 1.0" . "\r\n";
            $headers   .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $headers   .= "To: $prename $surname <$email>" . "\r\n";
            $headers   .= "From: activation@lima.zone" . "\r\n";
            $headers   .= "Reply-To: activation@lima.zone" . "\r\n";
            $headers   .= "X-Mailer: PHP/" . phpversion() . "\r\n";

            // Definition of Website Host
            $url = $this->url;

            $message    = <<<HTML
<!DOCTYPE html><html style="font-family: Roboto, Noto, sans-serif;color: #212121;margin: 0 auto;min-width: 319px;"><head> <title>Passwort - Wiederherstellung</title> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes"> <style>html, body{font-family: Roboto, Noto, sans-serif; color: #212121; margin: 0 auto; min-width: 319px;}#content{width: 90%; max-width: 600px; height: auto; margin: 0 auto 58px auto; text-align: left;}#footer{margin: 0 auto; width: 100%; height: 48px; background-color: #212121; color: white; text-align: center;}#footText{font-size: 13px; padding-top: 8px;}#pseudoButton{background-color: #2196F3; height: 48px; width: auto; text-align: center; font-size: 22px; color: white; -webkit-transition: background-color 0.2s; -moz-transition: background-color 0.2s; -ms-transition: background-color 0.2s; -o-transition: background-color 0.2s; transition: background-color 0.2s; border-radius: 5px; margin: 0 auto;}#pseudoButton:hover{background-color: #64B5F6; cursor: pointer;}a{color: #33691E; text-decoration: none; -webkit-transition: color 0.2s; -moz-transition: color 0.2s; -ms-transition: color 0.2s; -o-transition: color 0.2s; transition: color 0.2s;}a:hover{color: #558B2F;}#buttonLink{color: white; text-decoration: none;}hr{width: 70%; border: none; border-bottom: 1px solid #E0E0E0; margin: 20px auto;}#disclaimer{font-size: 14px; color: #616161;}/** Fix for apple clients */ @media only screen and (min-device-width: 601px){#content{width: 600px !important;}}</style></head><body style="font-family: Roboto, Noto, sans-serif;color: #212121;margin: 0 auto;min-width: 319px;"><!--[if (get mso 9)|(IE)]><div id="content" style="width: 600px;height: auto; margin: 96px auto 58px auto;text-align: left;"><![endif]--><div id="content" style="width: 90%;max-width: 600px;height: auto;margin: 0 auto 58px auto;text-align: left;"> <h1 style="text-align: center;">Hallo, $prename $surname!</h1> <p style="font-size: 16px;"> <br>Am $date um $time Uhr wurde Ihr Accountpasswort f&uuml;r die Raumreservierung ge&auml;ndert. Wenn Sie Ihr Passwort selbst ge&auml;ndert haben, k&ouml;nnen Sie diese E-Mail ignorieren / l&ouml;schen. </p><hr style="width: 70%;border: none;border-bottom: 1px solid #E0E0E0;margin: 20px auto;"> <p style="font-size: 14px;"> Sollten Sie Ihr Passwort <b>nicht</b> selbst ge&auml;ndert haben, hat m&ouml;glicherweise ein Dritter Zugriff auf Ihren Account erlangt und das zugeh&ouml;rige Passwort ge&auml;ndert.<br><br><span style="color: #D50000;"> <b> Melden Sie diesen Vorfall unverz&uuml;glich einem Administrator der Raumreservierung, unbefugte Personen k&ouml;nnen mit einem gestohlenen Account gro&szlig;en Schaden anrichten! </b> </span> </p><hr style="width: 70%;border: none;border-bottom: 1px solid #E0E0E0;margin: 20px auto;"> <p id="disclaimer" style="font-size: 13px;color: #616161;"> <b>Information</b><br>Diese E-Mail wurde im Rahmen der Accountverwaltung der Raumreservierung des <a href="https://gymnasium-klotzsche.de" title="www.gymnasium-klotzsche.de" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Gymnasiums Dresden-Klotzsche</a> versandt.<br>Wenn Sie diese E-Mail nicht angefordert haben, dann ignorieren Sie sie einfach.<br><br>Sollten Sie weitere Fragen oder Probleme haben, k&ouml;nnen Sie sich direkt an das <a href="https://gymnasium-klotzsche.de" title="www.gymnasium-klotzsche.de" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Gymnasium Dresden-Klotzsche</a> oder die <a href="$url#imprint" title="$url#imprint" target="_blank" style="color: #33691E;text-decoration: none;-webkit-transition: color 0.2s;-moz-transition: color 0.2s;-ms-transition: color 0.2s;-o-transition: color 0.2s;transition: color 0.2s;">Raumreservierung</a> wenden. <br><br>Wir entschuldigen uns f&uuml;r jedwede Art von Unannehmlichkeiten.<br>Ihr Team der Raumreservierung des Gymnasiums Dresden Klotzsche :-) </p></div><div id="footer" style="margin: 0 auto;width: 100%;height: 48px;background-color: #212121;color: white;text-align: center;"> <p id="footText" style="font-size: 12px;padding-top: 9px;"> Raumreservierung &copy; 2017 by<br>Moritz Menzel, Benjamin Kirchhoff, Maximilian Seiler </p></div></body></html>
HTML;
            return mail($to, $subject, $message, $headers);
        }

        public function selfChangePassword($name, $old, $new) {

            $sql = "SELECT password, salt, iterations, email FROM accounts_users WHERE name=:accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            $res = $r->fetchAll();
            if(!empty($res)) {
                $data = $res[0];
                $userPW = hash_pbkdf2('sha512', $old, $data['salt'], $data['iterations'], 255);
                if(hash_equals($data['password'], $userPW)) {
                    // Old PW correct, change PW
                    if($this->sendPasswordChangeMail($name, $data['email']) == true){
                        return $this->changePassword($name, $new);
                    }
                } else {
                    // Old PW incorrect
                    return array("error" => true, "message" => "Das eingegebene (alte) Passwort ist falsch, bitte überprüfen Sie Ihre Eingaben!");
                }
            } else {
                return array("error" => true, "message" => "Es ist ein Fehler beim Auslesen der Nutzerdaten aufgetreten!");
            }

        }

        public function isUserActivated($name){
            $sql = "SELECT status FROM accounts WHERE accounts.name=:accname";
            $r = $this->pdo->prepare($sql);
            $r->execute(array(":accname" => $name));
            $res = $r->fetchAll();
            if(!empty($res)) {
                return ($res[0]['status'] == 3);
            } else {
                return false;
            }
        }

    }
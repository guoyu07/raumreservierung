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
            //The Variable Contents have to be checked before calling the function!
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
                    return array("error" => true, "message" => "Fehler: Die Best&auml;tigungs-Mail konnte aufgrund eines Fehlers nicht gesendet werden!");
                }
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Es ist ein Fehler aufgetreten: ".$e->getMessage());
            }
        }

        private function sendConfirmationMail($activationcode, $name, $email)
        {
            $to         = $email;
            $subject    = "Gykl Raumreservierung - Anmeldung";

            $headers    = "MIME-Version: 1.0" . "\r\n";
            $headers   .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $headers   .= "To: $name <$email>" . "\r\n";
            $headers   .= "From: activation@lima.zone" . "\r\n";
            $headers   .= "Reply-To: activation@lima.zone" . "\r\n";
            $headers   .= "X-Mailer: PHP/" . phpversion() . "\r\n";

            $message    = <<<HTML
<!DOCTYPE html>
<html>
<head>
<title>Account - Aktivierung</title>
<meta charset=utf-8>
<style>

</style>
</head>
<body>
<!-- MINIFIED HTML CONTENT COMING SOON -->
</body>
</html>
HTML;

            mail($to, $subject, $message, $headers);
            return true;    //TODO: Change to 'return mail()' again but Webserver Times @ LimaCity are too disgusting
        }

        public function deactivateUser($name)
        {
            $sql1 = "UPDATE accounts SET last_status_change=NULL, email_confirmed=0, activationcode=NULL, status=1 WHERE name=:accname";
            $sql2 = "UPDATE accounts_users SET password=:newpw, salt=:newsalt, iterations=:newiterations, email=NULL WHERE name=:accname";

            $pwgen = $this->hash_password("gykl@2016");
            $pw = $pwgen['password'];
            $salt = $pwgen['salt'];
            $it = $pwgen['iterations'];

            $r1 = $this->pdo->prepare($sql1);
            $r2 = $this->pdo->prepare($sql2);

            try {

                $this->pdo->beginTransaction();
                $r1->execute(array(":accname" => $name));
                $r2->execute(array(":newpw" => $pw, ":newsalt" => $salt, ":newiterations" => $it, ":accname" => $name));
                $this->pdo->commit();

                return array("error" => false);

            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return array("error" => true, "message" => "Es ist ein Fehler aufgetreten: ".$e->getMessage());
            }
        }

        public function confirmUser($name, $code){

            $sql1 = "SELECT status, email_confirmed, activationcode, email FROM accounts, accounts_users
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
                        if($res['activationcode'] == $code){
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
                            return array("error" => true, "message" => "Fehler: Der Best&auml;tigungscode ist nicht korrekt!");
                        }
                    } elseif($res['status'] == 3) {
                        return array("error" => true, "message" => "Der Account ($name) wurde bereits aktiviert!");
                    } else {
                        return array("error" => true, "message" => "Der Account ($name) ist momentan deaktiviert!");
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
                return array("error" => true, "message" => "Fehler beim &Auml;ndern der Nutzerdaten: ".$e->getMessage());
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
                $res[$i]['text'] = preg_replace("/::NEWLINE::/", ' --- ', $res[$i]['text']);
                $res[$i]['text'] = preg_replace("/::AMP::/", "&", $res[$i]['text']);
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
                $r->execute(array(":pw" => $pw, ":salt" => $salt, ":iterations" => $iterations, ":name" => $name));
                $this->pdo->commit();
                return array("error" => false);
            } catch( PDOException $e ) {
                return array("error" => true, "message" => "Es ist ein Fehler beim Ändern des Passwortes aufgetreten: ".$e->getMessage());
            }
        }

    }
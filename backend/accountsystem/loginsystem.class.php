<?php

    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 18.12.16
     * Time: 22:40
     */

    class loginSystem
    {
        public function __construct($name, $pw, $pdo){

            $this->session = $_SESSION;
            $this->username = $name;
            $this->pw = $pw;
            if(is_a($pdo, 'PDO')) {
                $this->pdo = $pdo;
            } else {
                $this->pdo = false;
            }

        }
        public function login(){

            if($this->pdo){

                $sql = "SELECT accounts_users.name, accounts_users.password, accounts_users.salt, accounts_users.iterations, accounts_users.email
                        ,accounts.type, accounts.status, accounts.last_status_change
                        FROM accounts_users, accounts
                        WHERE accounts.name = accounts_users.name
                        AND accounts_users.name = :accname";
                $r = $this->pdo->prepare($sql);

                $sql2 = "UPDATE accounts SET accounts.last_login=NOW()
                         WHERE accounts.name = :accname";
                $r2 = $this->pdo->prepare($sql2);

                try{
                    $r->execute(array(":accname" => $this->username));
                    $r2->execute(array(":accname" => $this->username));
                    $res = $r->fetchAll();

                    if(!empty($res)){
                        $res = $res[0];
                    }
                    if(isset($res['name'])){
                        $hash = hash_pbkdf2("sha512", $this->pw, $res['salt'], $res['iterations'], 255);

                        if(hash_equals($res['password'], $hash)){

                            if($res['last_status_change'] != null && $res['status'] == 2) {
                                if((strtotime($res['last_status_change']) + (3600*12)) < time()) {
                                    // Not Log In , deactivate account
                                    require_once('userManagement.class.php');
                                    $um = new userManagement($this->pdo);
                                    $rae = $um->resetAccount($res['name']);

                                    $_SESSION['loggedin'] = false;

                                    if($rae['error'] == false) {
                                        return array("login" => false, "message" => "Ihre Aktivierungsfrist von 12 Stunden ist abgelaufen! Ihr Konto wurde wieder zur&uuml;ckgesetzt!");
                                    } else {
                                        return array("login" => false, "error" => true, "message" => "Ihre Aktivierungsfrist von 12 Stunden ist abgelaufen, doch beim Deaktivieren Ihres Accounts ist ein Fehler aufgetreten:<br><br>".$rae['message']);
                                    }
                                } else {
                                    $_SESSION['loggedin'] = true;
                                    $_SESSION['name'] = $res['name'];
                                    $_SESSION['acctype'] = $res['type'];
                                    $_SESSION['accstatus'] = $res['status'];
                                    $_SESSION['last_status_change'] = $res['last_status_change'];
                                    $_SESSION['email'] = $res['email'];

                                    return array("login" => true);
                                }
                            } else {
                                $_SESSION['loggedin'] = true;
                                $_SESSION['name'] = $res['name'];
                                $_SESSION['acctype'] = $res['type'];
                                $_SESSION['accstatus'] = $res['status'];
                                $_SESSION['last_status_change'] = $res['last_status_change'];
                                $_SESSION['email'] = $res['email'];

                                return array("login" => true);
                            }

                        } else {
                            return array("login" => false, "message" => "Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihr Passwort!");
                        }
                    } else {
                        return array("login" => false, "message" => "Fehler: Der eingegebene Name konnte nicht gefunden werden!");
                    }

                } catch(Exception $e){
                    return array("login" => false, "error" => true, "message" => "Ein Fehler bei der Datenbankverbindung ist aufgetreten<br>(".$e->getMessage().")");
                }

            } else {
                return array("login" => false, "message" => "Es ist Skript-Fehler (loginsystem.php.class) aufgetreten.<br>Bitte melden Sie diesen Fehler bei einem Administrator!");
            }

        }

    }
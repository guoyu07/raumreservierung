<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 30.01.17
     * Time: 18:15
     */

    require_once('../../backend/db/conf/dbconf.php');

    $ip = $_SERVER['REMOTE_ADDR'];

    function format ($t) {
        return date('d.m.Y H:i:s', $t);
    }

    $log = "";

    $start = time();
    $startF = format($start);
    $log .= "$startF\nStarted Cronjob\n";
    $log .= "Access by [$ip]\n";

    $sql = "SELECT name, activationcode, status, last_status_change, email_confirmed, last_email FROM accounts";
    $r = $pdo->prepare($sql);

    $log .= "Reading data from database\n";

    $status = $r->execute();

    if($status) {

        $res = $r->fetchAll();

        $log .= "-> Got response from database\n";

        if(!empty($res)) {

            $log .= "---> Response not empty\nStarting validity check routine\n";

            $confirmationTimeOver = array();
            $passwordResetTimeOver = array();

            foreach ($res as $user) {
                if($user['activationcode'] != null) {

                    if($user['status'] == 2) {
                        // Confirmation Mail
                        if($user['last_status_change'] != NULL) {
                            if((strtotime($user['last_status_change'])) + (3600*12) < $start) {
                                // Activation time has run out
                                array_push($confirmationTimeOver, $user['name']);
                                $log .= "-- account confirmation time for user ".$user['name']." has run out\n";
                            }
                        }

                    } elseif($user['status'] == 3) {
                        // Reset Password
                        if($user['last_email'] != NULL) {
                            if((strtotime($user['last_email']) + (3600 * 24)) < $start) {
                                // Password Reset Time has run out (new password restore request available)
                                array_push($passwordResetTimeOver, $user['name']);
                                $log .= "-- passwort restoration timer has run out for user ".$user['name']."\n";
                            }
                        }

                    }

                }
            }

            $log .= "\nReading finished, staring updating routine\n";
            if(!empty($confirmationTimeOver)) {

                require_once ('../accountsystem/userManagement.class.php');
                $um = new userManagement($pdo);

                $log .= "-> Updating last_status_change and status for invalid accounts\n";

                foreach($confirmationTimeOver as $user) {
                    $status = $um->resetAccount($user);
                    if($status['error'] == true) {
                        $log .= "--> failed for user $user with report ".$status['message']."\n";
                    } else {
                        $log .= "--> success for user $user\n";
                    }
                }

            } else { $log .= "-> No Accounts with invalid confirmation timers detected\n"; }

            if(!empty($passwordResetTimeOver)) {

                $log .= "-> Updating last_email for valid accounts\n";
                $sql = "UPDATE accounts SET last_email=NULL WHERE name=:accname";
                $r = $pdo->prepare($sql);
                foreach($passwordResetTimeOver as $user) {
                    try {
                        $pdo->beginTransaction();
                        $stat = $r->execute(array(":accname" => $user));
                        if($stat) {
                            $log .= "--> successful for user $user\n";
                        } else {
                            $log .= "--> failed for user $user\n";
                        }
                        $pdo->commit();
                    } catch (PDOException $e) {
                        $this->pdo->rollBack();
                        $log .= "--> failed for $user with report '".$e->getMessage()."'\n";
                    }
                }

            } else { $log .= "-> No Accounts with valid password restoration timers detected\n"; }

            $log .= "\nUpdate Routine finished, clearing activationcodes for affected accounts\n";

            $c1 = array();

            foreach($confirmationTimeOver as $user) {
                if(!array_search($user, $passwordResetTimeOver)) {
                    array_push($c1, $user);
                }
            }

            $combined = array_merge($passwordResetTimeOver, $c1);

            $sql = "UPDATE accounts SET activationcode=NULL WHERE name=:accname";
            $r = $pdo->prepare($sql);

            $log .= "-> Name arrays created\n";

            if(!empty($combined)) {
                foreach($combined as $user) {

                    try {
                        $pdo->beginTransaction();
                        $stat = $r->execute(array(":accname" => $user));
                        if($stat) {
                            $log .= "--> resetted activationcode for user $user\n";
                        } else {
                            $log .= "--> error resetting activationcode for user $user\n";
                        }
                        $pdo->commit();
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $log .= "--> error resetting activationcode for user $user with report '".$e->getMessage()."'\n";
                    }

                }
            } else {
                $log .= "--> No users to reset activationcodes for\n";
            }


        } else {
            $log .= "--> Response from database is empty\n";
        }

    } else {
        $log .= "-> Reading data from database failed!\n";
    }

    $duration = date('s', time() - $start) . " seconds";

    $log .= "\nScript finished after $duration";

    $sql = "INSERT INTO cronjob (access_ip, cron_log) VALUES (:ip, :log)";
    $r = $pdo->prepare($sql);
    $r->execute(array(":ip" => $ip, ":log" => $log));
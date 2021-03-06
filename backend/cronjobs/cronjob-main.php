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

    // Indicator if any changes were made to the database
    $changes = false;

    $start = time();
    $startF = format($start);
    $log .= "$startF\nStarted Cronjob\n";
    $log .= "Access by [$ip]\n";

    $sql = "SELECT name, activationcode, status, last_status_change, email_confirmed, last_email FROM accounts";
    $r = $pdo->prepare($sql);

    $sql2 = "SELECT R_ID, datum FROM reservations WHERE datum < DATE(NOW()-1)";
    $r2 = $pdo->prepare($sql2);

    $log .= "Reading data from database\n";

    $status = $r->execute();
    $status2 = $r2->execute();

    if($status && $status2) {

        $res = $r->fetchAll();
        $res2 = $r2->fetchAll();

        $log .= "-> Got response from database\n";

        if(!empty($res)) {

            $log .= "--> Response not empty\nStarting validity check routine\n";

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
                                $log .= "---> account confirmation time has run out for user ".$user['name']."\n";
                            }
                        }

                    } elseif($user['status'] == 3) {
                        // Reset Password
                        if($user['last_email'] != NULL) {
                            if((strtotime($user['last_email']) + (3600 * 24)) < $start) {
                                // Password Reset Time has run out (new password restore request available)
                                array_push($passwordResetTimeOver, $user['name']);
                                $log .= "---> password restoration timer has run out for user ".$user['name']."\n";
                            }
                        }

                    }

                } else {
                    if($user['last_email'] != NULL) {
                        // last_email usually shouldnt be set on this point anymore
                        array_push($passwordResetTimeOver, $user['name']);
                        $log .= "---> found last_email timestamp without an activationcode provided for user ".$user['name']."\n";
                    }
                }
            }

            $log .= "\nReading finished, starting updating routine\n";
            if(!empty($confirmationTimeOver)) {

                require_once ('../accountsystem/userManagement.class.php');
                $um = new userManagement($pdo);

                $changes = true;

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

                $changes = true;

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

                $changes = true;

                foreach($combined as $user) {

                    try {
                        $pdo->beginTransaction();
                        $stat = $r->execute(array(":accname" => $user));
                        if($stat) {
                            $log .= "--> reset activationcode for user $user\n";
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

            if(!empty($res2)) {

                $changes = true;
                $log .= "\nStarting deletion of invalid reservations\n";
                $s = "DELETE FROM reservations WHERE reservations.R_ID=:ID";
                $dr = $pdo->prepare($s);
                foreach($res2 as $reservation) {
                    $dr->execute(array(":ID" => $reservation['R_ID']));
                }
                $log .= "-> Finished deletion of ".count($res2)." invalid reservations\n";

            } else {
                $log .= "--> No (invalid) reservations in Database\n";
            }


        } else {
            $log .= "--> Fetching of users failed\n";
        }

    } else {
        $log .= "-> Reading data from database failed!\n";
    }

    $duration = date('s', time() - $start) . " seconds";

    $log .= "\nScript finished after $duration";

    $sql = "INSERT INTO cronjob (access_ip, cron_log, changes) VALUES (:ip, :log, :changes)";
    $r = $pdo->prepare($sql);
    $r->execute(array(":ip" => $ip, ":log" => $log, ":changes" => intval($changes)));
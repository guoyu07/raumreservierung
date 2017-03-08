<?php
    /**
     * Created by PhpStorm.
     * User: moritz
     * Date: 30.12.16
     * Time: 16:59
     */

    require_once('../accountsystem/sessioncontroller.class.php');
    require_once('../db/conf/dbconf.php');
    $sess = new SessionController($pdo);

    if(isset($_SESSION['name']) && $_SESSION['loggedin'] == true && $_SESSION['acctype'] == 1 && $_SESSION['accstatus'] == 3) {
        if(isset($_POST['request'])) {
            switch($_POST['request']) {
                case "getAllUsers":
                    require_once('../accountsystem/userManagement.class.php');
                    require_once('../db/conf/dbconf.php');
                    $um = new userManagement($pdo);
                    $res = $um->getAllUsers();
                    if($res['error'] == false && isset($res['response'])) {
                        echo json_encode(array("success" => true, "data" => $res['response']));
                    } else {
                        echo json_encode(array("success" => false, "message" => $res['message']));
                    }
                    break;
                case "update":
                    //Update Routine For Data Change in UserTable

                    if(isset($_POST['oldname']) && isset($_POST['newname']) && isset($_POST['newtype']) && isset($_POST['newstatus'])){

                        if($_SESSION['name'] == $_POST['oldname']){
                            echo json_encode(array("success" => false, "message" => "Sie können sich nicht selbst bearbeiten!"));
                        } else {
                            require_once('../accountsystem/userManagement.class.php');
                            require_once('../db/conf/dbconf.php');
                            $um = new userManagement($pdo);
                            $res = $um->updateUserData($_POST['oldname'], $_POST['newname'], $_POST['newtype'], $_POST['newstatus']);

                            if(!empty($res)) {
                                if(!$res['error'] && isset($res['response'])) {
                                    echo json_encode(array("success" => true, "data" => $res['response']));
                                } else {
                                    echo json_encode(array("success" => false, "message" => $res['message']));
                                }
                            } else {
                                echo json_encode(array("success" => false, "message" => "FATAL ERROR: Es wurde kein Ergebnis zurückgegeben! Bitte kontaktieren Sie umgehend einen Administrator!"));
                            }
                        }

                    } else {
                        echo json_encode(array("success" => false, "message" => "Es wurden nicht genug Daten übermittelt!"));
                    }
                    break;
                case "delete":
                    //Deletion Routing for User Deletion

                    if(isset($_POST['names'])) {
					
                        $names = json_decode($_POST['names']);						
                        if(!empty($names)) {
						
							if(in_array($_SESSION['name'], $names)){
								echo json_encode(array("success" => false, "message" => "Sie können sich nicht selber löschen!"));
							} else {

								require_once('../accountsystem/userManagement.class.php');
								require_once('../db/conf/dbconf.php');

								$um = new userManagement($pdo);
								$results = array();

								foreach($names as $name) {
									array_push($results, $um->deleteUser($name));
								}

								$check = true;
								$combinedMessages = "";

								foreach($results as $res){
									if($res['error'] == true){
										$check = false;
										$combinedMessages = $res['message']."<br>";
									}
								}

								if($check === true) {
									echo json_encode(array("success" => true, "data" => $results[count($results)-1]['data']));
								} else {
									echo json_encode(array("success" => false, "message" => "Es sind Fehler aufgetreten:<br>".$combinedMessages));
								}
							}	

                        } else {
							echo json_encode(array("success" => false, "message" => "Sie können sich nicht selber löschen!"));
						}
                    } else {
                        echo json_encode(array("success" => false, "message" => "Es wurden keine zu löschenden Namen angegeben!"));
                    }
                    break;
                case "add":
                    //Routine for Adding a new User
                    if(isset($_POST['name']) && isset($_POST['type'])){
                        if($_POST['type'] < 1 || $_POST['type'] > 3) {

                        } else {

                            /** STANDARD PASSWORD FOR EVERY NEWLY CREATED USER!!! */
                            $password = "gykl@2016";
                            $status = 1;                        //1 = Deactivated :)

                            $name = $_POST['name'];
                            $type = intval($_POST['type']);
                            require_once('../accountsystem/userManagement.class.php');
                            require_once('../db/conf/dbconf.php');
                            $um = new userManagement($pdo);
                            $erg = $um->addUser($name, $password, $type, $status);

                            if($erg['error']){
                                echo json_encode(array("success" => false, "message" => "Der Nutzer konnte nicht erstellt werden: <br>".$erg['message']));
                            } else {

                                $data = $um->getAllUsers();

                                if($data['error']){
                                    echo json_encode(array("success" => false, "message" => "Es ist ein Fehler bei der Datenbankabfrage aufgetreten: <br>".$data['message']));
                                }
                                else {
                                    echo json_encode(array("success" => true, "data" => $data['response']));
                                }

                            }

                        }
                    } else {
                        echo json_encode(array("success" => false, "message" => "Fehler: Es wurden nicht genügend Daten übermittelt!"));
                    }

                    break;
                case "reset":
                    // Routine for resetting (multiple) user's account(s)
                    if(isset($_POST['names'])) {
                        $names = json_decode($_POST['names']);
                        if(!empty($names)) {

                            if(in_array($_SESSION['name'], $names)){
                                echo json_encode(array("success" => false, "message" => "Sie können sich nicht selber bearbeiten!"));
                            } else {

                                require_once('../accountsystem/userManagement.class.php');
                                require_once('../db/conf/dbconf.php');

                                $um = new userManagement($pdo);
                                $results = array();

                                foreach($names as $name) {
                                    array_push($results, $um->resetAccount($name));
                                }

                                $check = true;
                                $combinedMessages = "";

                                foreach($results as $res){
                                    if($res['error'] == true){
                                        $check = false;
                                        $combinedMessages = $res['message']."<br>";
                                    }
                                }

                                if($check === true) {
                                    $data = $um->getAllUsers();
                                    if(!$data['error']) {
                                        echo json_encode(array("success" => true, "data" => $data['response']));
                                    } else {
                                        echo json_encode(array("success" => false, "message" => $data['message']));
                                    }
                                } else {
                                    echo json_encode(array("success" => false, "message" => "Es sind Fehler aufgetreten:<br>".$combinedMessages));
                                }
                            }

                        } else {
                            echo json_encode(array("success" => false, "message" => "Sie können sich nicht selbst bearbeiten!"));
                        }
                    } else {
                        echo json_encode(array("success" => false, "message" => "Es wurden keine zu bearbeitenden Namen übermittelt!"));
                    }
                    break;
                default:
                    echo json_encode(array("success" => false, "message" => "Die Anfrage konnte nicht gefunden werden!"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Es sind zu wenig Daten angegeben worden!"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Sie müssen (als Administrator) eingeloggt sein, um diese Aktion durchführen zu können!", "sessionError" => true));

    }
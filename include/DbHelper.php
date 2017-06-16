<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbHelper
 *
 * @author abert
 */
class DbHelper {

    //put your code here
    private $con;

    function __construct() {
        require_once dirname(__FILE__) . '/dbConnect.php';

        $db = new DbConnect();
        $this->con = $db->connect();
    }

//check if the user exist
    public function studentLogin($reg_number, $password) {
        // $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM student_auth WHERE registration_auth=? and password_auth=?");
        $stmt->bind_param("ss", $reg_number, $password);
        $stmt->execute();
        $stmt->store_result();
        //Getting the result
        $num_rows = $stmt->num_rows;
        //closing the statment
        $stmt->close();
        return $num_rows > 0;
    }

    //if exists fetch the user details
    public function user_details($registration_number) {
        $stmt = $this->con->prepare("SELECT * FROM student where registration_number=?");
        $stmt->bind_param('s', $registration_number);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $student;
    }

}

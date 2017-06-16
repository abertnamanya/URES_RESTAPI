<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dbConnect
 *
 * @author abert
 */
//require_once '../include/connection.php';

class dbConnect {

    private $con;

    //put your code here
    function __construct() {
        
    }

    public function connect() {
        include_once dirname(__FILE__) . '/connection.php';
        $this->con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if (mysqli_connect_errno($this->con)) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        return $this->con;
    }

}

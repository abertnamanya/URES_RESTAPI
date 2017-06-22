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

    //machine timestamp
    function getDatetimeNow() {
        $tz_object = new DateTimeZone('EAT');
        $datetime = new DateTime();
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y\-m\-d');
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

    //registered academic years
    public function studentAcademicYears($student_id) {
        $stmt = $this->con->prepare('select *,ra._when_added as time_stamp from registered_academic_years ra LEFT JOIN academic_years ac ON(ra.academic_years_years_id=ac.academic_year_id)'
                . ' LEFT JOIN semesters s ON(ra.semester_semester_id=s.semester_id) where student_student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //first student details
    public function student_details($student_id) {
        $stmt = $this->con->prepare('select universities_university_id,courses_course_id from student where student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $data;
    }

    //all university academic years
    public function university_academic_years($university_id) {
        //university  academic years
        $stmt = $this->con->prepare('select * from academic_years where university_university_id=?');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function semesters() {
        $stmt = $this->con->prepare('select * from semesters');
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //check if the student already registered

    public function isAlreadyRegistered($student_id, $academic_year, $semester) {
        $stmt = $this->con->prepare('select * from registered_academic_years where student_student_id=? && academic_years_years_id=? && semester_semester_id=?');
        $stmt->bind_param('sss', $student_id, $academic_year, $semester);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //student regiatration/academic year
    public function studentRegistration($student_id, $academic_year, $semester) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('INSERT INTO registered_academic_years(student_student_id,academic_years_years_id,semester_semester_id,_when_added)'
                . 'VALUES(?,?,?,?)');
        $stmt->bind_param('ssss', $student_id, $academic_year, $semester, $time_stamp);
        $stmt->execute();
        $stmt->close();
    }

    //course units
    public function course_units($student_id, $register_year_id) {
        $stmt = $this->con->prepare('select * from registered_course_units rc left join course_units c ON(rc.course_units_units_id=c.course_unit_id) where student_student_id=? and registered_academic_years_id=?');
        $stmt->bind_param('ss', $student_id, $register_year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

//all course units
    public function fetch_course_units($course_id) {
        $stmt = $this->con->prepare('select * from course_units where courses_course_id=?');
        $stmt->bind_param('s', $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //register units

    public function registerUnits($unit_id, $year_id, $student_id) {
        $stmt = $this->con->prepare('insert into registered_course_units(course_units_units_id,registered_academic_years_id,student_student_id)'
                . 'values(?,?,?)');
        $stmt->bind_param('sss', $unit_id, $year_id, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    //check if course unit already registered


    public function UnitExists($unit_id, $student_id) {
        $stmt = $this->con->prepare('select * from registered_course_units where course_units_units_id=? && student_student_id=?');
        $stmt->bind_param('ss', $unit_id, $student_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //get marks academic years and semesters


    public function fetch_marks($student_reg, $university_id, $year_id, $semester_id) {
        $stmt = $this->con->prepare('select * from marks where reg_number=? && university_university_id=? && academic_years_year_id=? && semesters_semester_id=?');
        $stmt->bind_param('ssss', $student_reg, $university_id, $year_id, $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //submit student complaints
    public function student_complaint($student_id, $title, $description) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('insert into complaints(title,complaint,student_student_id,_when_added)'
                . 'VALUES(?,?,?,?)');
        $stmt->bind_param('ssss', $title, $description, $student_id, $time_stamp);
        $stmt->execute();
        $stmt->close();
    }

    //chat
    //Function to store gcm registration token in database
    public function storeGCMToken($student_id, $token) {
        $stmt = $this->con->prepare("INSERT INTO tokens(token,student_student_id) VALUES(?,?)");
        $stmt->bind_param("ss", $token, $student_id);
        if ($stmt->execute())
            return true;
        return false;
    }

    //sending message to other devices
    public function getRegistrationTokens($student_id) {
        $stmt = $this->con->prepare("SELECT token FROM tokens WHERE NOT student_student_id = ?;");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tokens = array();
        while ($row = $result->fetch_assoc()) {
            array_push($tokens, $row['token']);
        }
        return $tokens;
    }

    //Function to add message to the database
    public function addMessage($student_id, $message) {
//        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare("INSERT INTO messages (message,student_student_id) VALUES (?,?)");
        $stmt->bind_param("ss", $message, $student_id);
        if ($stmt->execute())
            return true;
        return false;
    }

    //Function to get messages from the database 
    public function getMessages() {
        $stmt = $this->con->prepare("SELECT message_id,message,student_student_id,firstName,m._when_added as time_stamp FROM messages m, student s WHERE m.student_student_id = s.student_id ORDER BY message_id ASC;");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }
   //test changes peret
}

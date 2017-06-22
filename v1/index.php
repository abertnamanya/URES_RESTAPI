<?php

require '../vendor/autoload.php';
require '../include/DbHelper.php';
require '../libs/gcm.php';

use Slim\App;

$app = new App();


//student login function 
$app->post('/login', function ($request, $response, $args) {
    //params fron the mobile app
    $reg_number = $request->getParam('reg_no');
    $password = $request->getParam('password');
    $db = new DbHelper();
    //add an array to be sent to the json request
    $feed = array();
    //check for correct user
    if ($db->studentLogin($reg_number, $password)) {

        //request student detail
        $student = $db->user_details($reg_number);

        $data['student_id'] = $student['student_id'];
        $data['firstName'] = $student['firstName'];
        $data['lastName'] = $student['lastName'];
        $data['registration_number'] = $student['registration_number'];
        $data['university_id'] = $student['universities_university_id'];
        $data['status'] = true;
        $data['message'] = "Login successfull";
        array_push($feed, $data);
        echoRespnse(200, $feed);
    } else {
        $data['status'] = false;
        $data['message'] = "login failed";
        array_push($feed, $data);
        echoRespnse(200, $feed);
    }
});
//fetch student academic years registed
$app->post('/registered_years', function ($request, $response, $args) {
    $db = new DbHelper();
    $student_id = $request->getParam('student_id');
    $result = $db->studentAcademicYears($student_id);

    $res = array();
    while ($row = $result->fetch_assoc()) {
        $data['id'] = $row['registered_academic_years_id'];
        $data['academic_year'] = $row['academic_year'];
        $data['semester'] = $row['semester'];
        $data['academic_years_years_id'] = $row['academic_years_years_id'];
        $data['semester_semester_id'] = $row['semester_semester_id'];
        $data['time_stamp'] = date("F Y", strtotime($row['time_stamp']));
        //date("jS F, Y", strtotime("11/12/10"))
        array_push($res, $data);
    }
    echoRespnse(200, $res);
});

//all university academic years
$app->post("/academic_years", function ($request, $response, $args) {
    $student_id = $request->getparam('student_id');
    $db = new DbHelper();
    //return student university
    $data = $db->student_details($student_id);
    $result = $db->university_academic_years($data['universities_university_id']);
    $res = array();
    while ($row = $result->fetch_assoc()) {
        $query['id'] = $row['academic_year_id'];
        $query['year'] = $row['academic_year'];
        array_push($res, $query);
    }
    echoRespnse(200, $res);
});

$app->post("/semesters", function() {
    $db = new DbHelper();
    $result = $db->semesters();
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $data['id'] = $row['semester_id'];
        $data['semester'] = $row['semester'];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});
//student registation
$app->post('/registration', function($request, $res, $args) {
    $student_id = $request->getParam('student_id');
    $year = $request->getParam('year');
    $semester = $request->getParam('semester');
    $db = new DbHelper();
    //check if the student already registered
    $check = $db->isAlreadyRegistered($student_id, $year, $semester);
    if ($check) {
        echoRespnse(201, "You have already registered");
    } else {
        $db->studentRegistration($student_id, $year, $semester);
        echoRespnse(200, "Registered successfully");
    }
});

require '../v1/courseUnits.php';
require '../v1/marks.php';
require '../v1/chat.php';

function echoRespnse($status_code, $response) {
    echo json_encode($response);
}

$app->run();
?>

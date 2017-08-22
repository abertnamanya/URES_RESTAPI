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
    if ($db->studentLogin($reg_number, $password)) {
        $response = $db->user_details($reg_number);
        echoRespnse(200, $response);
    } else {
        $array = array();
        $data['status'] = false;
        $data['message'] = "login failed";
        array_push($array, $data);
        echoRespnse(200, $array);
    }
});

$app->post('/changePassword', function($request, $res, $args) {
    $old_password = $request->getParam('old_password');
    $new_password = $request->getParam('new_password');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $result = $db->checkPassword($student_id, $old_password);
    //match the old password
    $response = array();
    if ($result) {
        $db->change_password($student_id, $new_password);
        $response['message'] = "Password changed successful";
    } else {
        $response['message'] = "Old password not matching";
    }
    echoRespnse(200, $response);
});



//all university academic years
$app->post("/academic_years", function ($request, $response, $args) {
    $student_id = $request->getparam('student_id');
    $db = new DbHelper();
    //return student university
    $data = $db->student_details($student_id);
    $stmt = $db->university_academic_years($data['universities_university_id']);
    $res = array();
    $stmt->bind_result($academic_year_id, $academic_year);
    while ($stmt->fetch()) {
        $query['id'] = $academic_year_id;
        $query['year'] = $academic_year;
        array_push($res, $query);
    }
    $stmt->close();
    echoRespnse(200, $res);
});

$app->post("/semesters", function() {
    $db = new DbHelper();
    $result = $db->semesters();
    echoRespnse(200, $result);
});

require '../v1/courseUnits.php';
require '../v1/marks.php';
require '../v1/chat.php';
require '../v1/campusNews.php';
require '../v1/campusEvents.php';
require '../v1/StudentRegistration.php';
require '../v1/counselling.php';
require '../v1/suggestionBox.php';

function echoRespnse($status_code, $response) {
    echo json_encode($response);
}

$app->run();
?>

<?php

//fetch student academic years registed
$app->post('/registered_years', function ($request, $response, $args) {
    $db = new DbHelper();
    $student_id = $request->getParam('student_id');
    $result = $db->studentAcademicYears($student_id);
    echoRespnse(200, $result);
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
        $registered_academic_years_id = $db->studentRegistration($student_id, $year, $semester);
        if ($registered_academic_years_id) {
            $db->register_units($student_id, $year, $semester, $registered_academic_years_id);
        }
        echoRespnse(200, "Registered successfully");
    }
});
?>

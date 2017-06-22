<?php

//registered course units
$app->post('/course_units', function($request, $res, $args) {
    $registerd_year_id = $request->getparam('registered_year_id');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $result = $db->course_units($student_id, $registerd_year_id);
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $data['code'] = $row['course_unit_code'];
        $data['unit'] = $row['course_unit'];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});

//fetch all course units
$app->post('/all_units', function($request, $res, $args) {
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $data = $db->student_details($student_id);
    //fetch courses
    $result = $db->fetch_course_units($data['courses_course_id']);
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $array['_id'] = $row['course_unit_id'];
        $array['unit'] = $row['course_unit'];
        array_push($response, $array);
    }
    echoRespnse(200, $response);
});

//register course units
$app->post('/register_units', function($request, $res, $args) {
    $unit_id = $request->getParam('unit_id');
    $year_id = $request->getParam('year_id');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $check = $db->UnitExists($unit_id, $student_id);
    if ($check) {
        echoRespnse(201, "Course unit already registered");
    } else {
        $db->registerUnits($unit_id, $year_id, $student_id);
        echoRespnse(200, "course units sent successfully");
    }
});
?>
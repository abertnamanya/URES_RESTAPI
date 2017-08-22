<?php

//registered course units
$app->post('/course_units', function($request, $res, $args) {
    $registerd_year_id = $request->getparam('registered_year_id');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $stmt = $db->course_units($student_id, $registerd_year_id);
    $response = array();
    $stmt->store_result();
    $response['status'] = false;
    $stmt->bind_result($course_unit_code, $course_unit);
    if ($stmt->num_rows > 0) {
        $response['status'] = true;
        $response['data'] = array();
        while ($stmt->fetch()) {
            $data['code'] = $course_unit_code;
            $data['unit'] = $course_unit;
            array_push($response['data'], $data);
        }
    } else {
        $response['status'] = false;
    }
    echoRespnse(200, $response);
});

$app->post('/non_registered_units', function($request, $res, $args) {
    $registerd_year_id = $request->getparam('registered_year_id');
    $student_id = $request->getParam('student_id');
    $university_id = $request->getParam('university_id');
    $student_reg = $request->getParam('student_reg');
    $response = array();
    $db = new DbHelper();
    $retakes = $db->fetch_retakes($student_reg, $university_id);

    $data_array = array();
    $response['status'] = false;
    if (count($data) > 0) {
        $response['data'] = array();
        $response['status'] = TRUE;
        foreach ($data as $row) {
            $data_array['course_unit'] = $row['course_unit'];
            array_push($response['data'], $data_array);
        }
    } else {
        $response['status'] = false;
    }
//    $stmt = $db->fetch_non_registered_units($registerd_year_id, $student_id);
//    $stmt->bind_result($data[0], $data[1], $data[2]);
//    $response = array();
//    $response['status'] = false;
//    $stmt->store_result();
//    if ($stmt->num_rows > 0) {
//        $response['status'] = true;
//        $response['data'] = array();
//        while ($stmt->fetch()) {
//            $array['id'] = $data[0];
//            $array['code'] = $data[1];
//            $array['unit'] = $data[2];
//            array_push($response['data'], $array);
//        }
//    } else {
//        $response['status'] = false;
//    }
//
//    $stmt->close();
//    echoRespnse(200, $response);
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

$app->post('/year_units', function($request, $res, $args) {
    $year_id = $request->getParam('year_id');
    $semester_id = $request->getParam('semester_id');
    $student_id = $request->getParam('student_id');

    $db = new DbHelper();
    $student_details = $db->fetch_student_course($student_id);
    $student_details->bind_result($course_id);
    $student_details->fetch();
    $student_details->close();
    $stmt = $db->academic_year_units($year_id, $semester_id, $course_id);
    $stmt->bind_result($data[0], $data[1]);
    $response = array();
    while ($stmt->fetch()) {
        $array['code'] = $data[0];
        $array['unit'] = $data[1];
        array_push($response, $array);
    }
    echoRespnse(200, $response);
});
?>
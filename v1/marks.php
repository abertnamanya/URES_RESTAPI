<?php

$app->post('/fetch_marks', function($request, $res, $args) {
    $student_reg = $request->getParam('student_reg');
    $university_id = $request->getParam('university_id');
    $year_id = $request->getParam('year_id');
    $semester_id = $request->getParam('semester_id');
    //get marks academic years
    $db = new DbHelper();
    $data = $db->fetch_marks($student_reg, $university_id, $year_id, $semester_id);
    $response = array();
    $data_array = array();
    $response['status'] = false;
    if (count($data) > 0) {
        $response['data'] = array();
        $response['status'] = TRUE;
        foreach ($data as $row) {
            $data_array['course_unit'] = $row['course_unit'];
            $data_array['mark'] = $row['mark'];
            $data_array['grade_value'] = $row['grade_value'];
            $data_array['value'] = $row['value'];
            $data_array['progress'] = $row['progress'];
            array_push($response['data'], $data_array);
        }
    } else {
        $response['status'] = false;
    }

    echoRespnse(200, $response);
});

//marks compliants
$app->post('/complaint', function($request, $res, $args) {
    $description = $request->getParam('description');
    $year = $request->getParam('year');
    $semester = $request->getParam('semester');
    $student_id = $request->getParam('student_id');
    $complaint_category = $request->getParam('complaint_category');
    $db = new DbHelper();
    $db->student_complaint($student_id, $title, $description, $complaint_category, $year, $semester);
    echo 'compliant submitted successfully';
});

//marks compliants
$app->post('/complaint_categories', function($request, $res, $args) {
    $title = $request->getParam('title');
    $university_id = $request->getParam('university_id');
    $db = new DbHelper();
    $stmt = $db->fetch_complait_categories($university_id);
    $stmt->store_result();
    $stmt->bind_result($data[0], $data[1]);
    $response = array();
    if ($stmt->num_rows > 0) {
        while ($stmt->fetch()) {
            $array['id'] = $data[0];
            $array['type'] = $data[1];
            array_push($response, $array);
        }
        echoRespnse(200, $response);
    } else {
        echo "no data found";
    }
});
//
$app->post('/my_complaints', function($request, $res, $args) {
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $response = array();
    $response['status'] = false;
    $stmt = $db->fetch_my_complaints($student_id);
    $stmt->store_result();
    $stmt->bind_result($data[0], $data[1], $data[2], $data[3]);
    if ($stmt->num_rows > 0) {
        $response['status'] = true;
        $response['data'] = array();
        while ($stmt->fetch()) {
            $com_data = array('id' => $data[0], 'complaint' => $data[1], 'type' => $data[2], 'status' => $data[3]);
            array_push($response['data'], $com_data);
        }
    } else {
        $response['status'] = false;
    }
    echoRespnse(200, $response);
});



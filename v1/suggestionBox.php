<?php

//all university academic years
$app->post("/suggestions", function ($request, $response, $args) {
    $university_id = $request->getparam('university_id');
    $db = new DbHelper();
    $stmt = $db->fetch_suggestions($university_id);
    $res = array();
    $stmt->bind_result($data[0], $data[1], $data[2]);
    while ($stmt->fetch()) {
        $query['id'] = $data[0];
        $query['suggestion'] = $data[1];
        $query['time_stamp'] = $data[2];
        array_push($res, $query);
    }
    $stmt->close();
    echoRespnse(200, $res);
});

$app->post('/post_suggestion', function($request, $response, $args) {
    $university_id = $request->getparam('university_id');
    $suggestion = $request->getparam('suggestion');
    $student_id = $request->getparam('student_id');
    $db = new DbHelper();
    $stmt = $db->insert_suggestion($suggestion, $university_id, $student_id);
    $res = array();
    echoRespnse(200, "Suggestion posted successfully");
});

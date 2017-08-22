<?php

$app->post('/counsellors', function($request, $res, $args) {
    $university_id = $request->getParam('university_id');
    $db = new DbHelper();
    $stmt = $db->fetch_counsellors($university_id);
    $response = array();
    $stmt->bind_result($arrray[0], $arrray[1], $arrray[2], $arrray[3]);
    while ($stmt->fetch()) {
        $data['firstName'] = $arrray[0];
        $data['lastName'] = $arrray[1];
        if ($arrray[2] > 75) {
            $data['online'] = false;
        } else {
            $data['online'] = true;
        }
        $data['user_id'] = $arrray[3];

        array_push($response, $data);
    }
    echoRespnse(200, $response);
});

<?php

$app->post('/campus_events', function($request, $res, $args) {
    $university_id = $request->getParam('university_id');
    $db = new DbHelper();
    $stmt = $db->fetch_events($university_id);
    $response = array();
    $stmt->bind_result($arrray[0], $arrray[1], $arrray[2], $arrray[3]);
    while ($stmt->fetch()) {
        $data['event_id'] = $arrray[0];
        $data['title'] = $arrray[1];
        $data['event_detail'] = str_replace(array("\n", "\r"), '', strip_tags($arrray[2]));
        $data['time_stamp'] = $arrray[3];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});

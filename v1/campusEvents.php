<?php


$app->post('/campus_events', function($request, $res, $args) {
    $university_id = $request->getParam('university_id');
    $db = new DbHelper();
    $result = $db->fetch_events($university_id);
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $data['event_id'] = $row['event_id'];
        $data['title'] = $row['title'];
        $data['event_detail'] = str_replace(array("\n", "\r"), '', strip_tags($row['event_detail']));
        $data['time_stamp'] = $row['time_stamp'];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});
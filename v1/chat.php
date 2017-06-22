<?php

//This will store the gcm token to the database
$app->post('/storegcmtoken', function ($request, $res, $args) {
    $token = $request->getParam('token');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $response = array();
    if ($db->storeGCMToken($student_id, $token)) {
        echo'success';
//        $response['error'] = false;
//        $response['message'] = "token stored";
    } else {
        echo 'Could not store token';
//        $response['error'] = true;
//        $response['message'] = "Could not store token";
    }
    //  echoRespnse(200, $response);
});


//chat groups


$app->post('/chat_groups', function($request, $res, $args) {
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $result = $db->fetch_chat_groups($student_id);
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $data['group_id'] = $row['group_id'];
        $data['group_name'] = $row['group_name'];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});

//register group

$app->post('/add_group', function($request, $res, $args) {
    $student_id = $request->getParam('student_id');
    $group_name = $request->getParam('group_name');
    $db = new DbHelper();
    $group_id = $db->create_group($group_name);
    //role
    $role = "admin";
    if ($group_id) {
        //register user as member(admin
        $db->register_members($student_id, $role, $group_id);
        echo 'Group created successfully';
    } else {
        echo 'Error has occurried';
    }
});






$app->post('/send', function ($request, $res, $args) {

    //Getting request parameters
    $student_id = $request->getParam('student_id');
    $message = $request->getParam('message');
    $name = $request->getParam('name');

    //Creating a gcm object
    $gcm = new gcm();

    //Creating db object
    $db = new DbHelper();

    //Creating response array
    $response = array();
    $res = array();
    //Creating an array containing message data
//    $pushdata = array();
    //Adding title which would be the username
    $res['data']['title'] = $name;
    //Adding the message to be sent
    $res['data']['message'] = $message;
    //Adding user id to identify the user who sent the message
    $res['data']['id'] = $student_id;
    //array('data', $pushdata);
    //If message is successfully added to database
    if ($db->addMessage($student_id, $message)) {
        //Sending push notification with gcm object

        $data = $gcm->sendMessage($db->getRegistrationTokens($student_id), $res);
        echo $data;
//        $response['message'] = $data;
//        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
//    echoRespnse(200, $response);
});

$app->get('/messages', function () use ($app) {
    $db = new DbHelper();
    $messages = $db->getMessages();
    $response = array();
    $response['error'] = false;
    $response['messages'] = array();
    while ($row = mysqli_fetch_array($messages)) {
        $temp = array();
        $temp['id'] = $row['message_id'];
        $temp['message'] = $row['message'];
        $temp['userid'] = $row['student_student_id'];
        $temp['sentat'] = $row['time_stamp'];
        $temp['name'] = $row['firstName'];
        array_push($response['messages'], $temp);
    }
    echoRespnse(200, $response);
});
?>
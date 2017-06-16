<?php

require '../vendor/autoload.php';
require '../include/DbHelper.php';

use Slim\App;
     
$app = new App();
$app->post('/login', function ($request, $response, $args) {
    $reg_number = $request->getParam('reg_no');
    $password = $request->getParam('password');
    $db = new DbHelper();
    if ($db->studentLogin($reg_number, $password)) {
        $student = $db->user_details($reg_number);
        $data['student_id'] = $student['student_id'];
        $data['firstName'] = $student['firstName'];
        $data['lastName'] = $student['lastName'];
        $res['status'] = true;
        $res['message'] = "Login successfull";
        echoRespnse(200, $data);
    } else {
        $res['status'] = false;
        $res['messgae'] = "login failed";
        echoRespnse(200, $res);
    }
});

function echoRespnse($status_code, $response) {
    echo json_encode($response);
}

$app->run();
?>

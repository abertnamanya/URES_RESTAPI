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
    if ($data->num_rows > 0) {
        while ($row = $data->fetch_assoc()) {
            $array['course_unit'] = $row['course_unit'];
            $array['mark'] = $row['mark'];
            $array['grade'] = $row['grade'];
            $array['cu'] = $row['cu'];
            array_push($response, $array);
        }
        echoRespnse(200, $response);
    } else {
        echo "no data found";
    }
});

//marks compliants
$app->post('/complaint', function($request, $res, $args) {
    $title = $request->getParam('title');
    $description = $request->getParam('description');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $db->student_complaint($student_id, $title, $description);
    echo 'compliant submitted successfully';
});





//$app->post('/upload', function ($request, $response, $args) {
//    $response = array();
//    $upload_path = '../../URES/assets/upload/complaints/';
//    
//    $audio_filename = basename( $_FILES['audiofile']['name']);
//
//	/*
//	**final file path, second parameter in the move_uploaded_file function
//	*/
//	$audio_file_path = $audio_file_path .$audio_filename;
//
//
//     /*
//     **method to move the file into the folder
//     */
//	if(move_uploaded_file($_FILES['audiofile']['tmp_name'], $upload_path)){
//		// sucesss
//	}else{
//        //failed
//	}
//    
//    
//    
////    $files =$request->getUploadedFiles();
////    $newFile = $files['file'];
//// if($newFile->getError()===UPLOAD_ERR_OK){
////    $uploadFileName=$newFile->getClientFilename();
////    $newFile->moveTo($upload_path.$uploadFileName);
//// }
//    echo json_encode($response);
//});

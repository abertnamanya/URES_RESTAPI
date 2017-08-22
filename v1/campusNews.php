<?php

$app->post('/latestNews', function($request, $res, $args) {
    $university_id = $request->getParam('university_id');
    $db = new DbHelper();
    $stmt = $db->fetch_latestNews($university_id);
    $stmt->bind_result($data[0], $data[1], $data[2], $data[3], $data[4]);
    $response = array();
    while ($stmt->fetch()) {
        $array['news_id'] = $data[0];
        $array['title'] = $data[1];
        $array['news_detail'] = str_replace(array("\n", "\r"), '', strip_tags($data[2]));
//        $array['news_detail'] = $data[2];
        $array['_when_added'] = $data[3];
        $array['image'] = $data[4];
        array_push($response, $array);
    }
    echoRespnse(200, $response);
});
//add my news

$app->post('/addMyNews', function($request, $res, $args) {
    $news_id = $request->getParam('news_id');
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    //check if already added
    $check = $db->newsExists($news_id, $student_id);
    if ($check) {
        echo 'Already added to my news';
    } else {
        $db->submitMyNews($news_id, $student_id);
        echo 'Favourite News added successfully';
    }
});

//fetch my news
$app->post('/myNews', function($request, $res, $args) {
    $student_id = $request->getParam('student_id');
    $db = new DbHelper();
    $result = $db->fetch_myNews($student_id);
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $data['news_id'] = $row['news_id'];
        $data['title'] = $row['title'];
        $data['news_detail'] = str_replace(array("\n", "\r"), '', strip_tags($row['news_detail']));
        $data['_when_added'] = $row['_when_added'];
        $data['category_name'] = $row['category_name'];
        $data['image'] = $row['image'];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});

//update the news views
$app->post('/updateViews', function($request, $res, $args) {
    $news_id = $request->getParam('news_id');
    //fetch existing views
    $db = new DbHelper();
    $count_views = $db->newsViews($news_id);
    $updateCount = $count_views['views_count'] + 1;
    $db->updateNews($news_id, $updateCount);
});

//fetch most views news
$app->post('/most_viewed', function($request, $res, $args) {
    $university_id = $request->getParam('university_id');
    $db = new DbHelper();
    $result = $db->fetch_most_viewd_News($university_id);
    $response = array();
    while ($row = $result->fetch_assoc()) {
        $data['news_id'] = $row['news_id'];
        $data['title'] = $row['title'];
        $data['news_detail'] = str_replace(array("\n", "\r"), '', strip_tags($row['news_detail']));
        $data['_when_added'] = $row['_when_added'];
        $data['category_name'] = $row['category_name'];
        $data['image'] = $row['image'];
        array_push($response, $data);
    }
    echoRespnse(200, $response);
});
?>


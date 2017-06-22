<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of gcm
 *
 * @author abert
 */
class gcm {

    //put your code here
    function __construct() {
        
    }

    //This function will send message to the given registration ids
    //We are also passing a message that is actually an array containing the message 
    public function sendMessage($registration_ids, $message) {
//        $msg = array
//            (
//            "body" => "test",
//            "title" => "URES",
//            "sound" => "mySound"
//        );
        $fields = array(
            'registration_ids' => $registration_ids,
//            'notification' => $msg,
            'data' => $message,
        );
        //In this function we are calling the main method responsible for sending push notification
        //it is sendPushNotification
        return $this->sendPushNotification($fields);
    }

    //This is the main method responsible for sending push notification
    //I have already explained it in previous tutorials 
    private function sendPushNotification($fields) {
        //include_once __DIR__ . '../include/connection.php';
        //  include '../include/connection.php';

        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . FIREBASE_API_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }

}

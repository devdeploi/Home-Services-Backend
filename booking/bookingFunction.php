<?php
include '../inc/dbcon.php';
use Twilio\Rest\Client;
require __DIR__ . '/../vendor/autoload.php'; 

function sendMessage($mobile_no, $message_body) {
    
    // $account_sid = 'ACccff51d8341c4a67233be225c273e54c';
    // $auth_token = 'db6d26653797e6abd7cabd6ef1e25609';
    // $twilio_number = '+12495460816';    
    $account_sid = 'ACf925523fa759276503aa19b35e709fb4';
$auth_token = '270537a1956b026ca28884c54705d6c8';
$twilio_number = '+19787880275';   

    // Initialize the Twilio client
    $twilio = new Client($account_sid, $auth_token);

    try {
        // Send custom message via Twilio
        $message = $twilio->messages->create(
            $mobile_no,
            [
                'from' => $twilio_number, 
                'body' => $message_body 
            ]
        );
        return $message->sid;
    } catch (Exception $e) {
        // Handle error and return false
        error_log("Twilio Error: " . $e->getMessage());
        return false;
    }
}
?>
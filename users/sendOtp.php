<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// require '..vendor/autoload.php';
 require __DIR__ . '/../vendor/autoload.php'; // This ensures the correct path
include ('userFunction.php');
include 'dbcon.php';

use Twilio\Rest\Client;

// SID and Auth Token from twilio.com/console
// $account_sid = 'ACccff51d8341c4a67233be225c273e54c';
// $auth_token = 'db6d26653797e6abd7cabd6ef1e25609';
// $twilio_number ='+12495460816';

$account_sid = 'ACf925523fa759276503aa19b35e709fb4';
$auth_token = '270537a1956b026ca28884c54705d6c8';
$twilio_number = '+19787880275';    

$twilio = new Client($account_sid, $auth_token);

$input = json_decode(file_get_contents("php://input"), true);

// $input = json_decode(file_get_contents("php://input"), true);
$mobile_no = $input['mobile_no'] ?? null;
$resend = $input['resend'] ?? false;

if ($mobile_no) {
    // Generate a 4-digit OTP
    $otp = rand(1000, 9999);

    // If resend is requested, use resendOtp function, otherwise use sendOtp
    $messageSid = $resend ? resendOtp($mobile_no, $otp) : sendOtp($mobile_no, $otp);

    if ($messageSid) {
        echo json_encode([
            "status" => 200,
            "result" => "true",
            "message" => $resend ? "OTP resent successfully" : "OTP sent successfully",
            "message_sid" => $messageSid
        ]);
    } else {
        echo json_encode([
            "status" => 500,
            "result" => "false",
            "message" => "Failed to send OTP"
        ]);
    }
} else {
    echo json_encode([
        "status" => 400,
        "result" => "false",
        "message" => "Mobile number is required"
    ]);
}



?>

<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 

include('userFunction.php');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);  // Preflight OK
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "POST") {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($inputData['mobile_no']) && isset($inputData['otp_code'])) {
        $mobile_no = $inputData['mobile_no'];
        $otp_code = $inputData['otp_code'];

        // Verify OTP
        $result = verifyOTP($mobile_no, $otp_code);

        // Log the result for debugging
        error_log("Verification Result: " . $result);

        // Clean output buffer and respond
        ob_clean();
        echo $result;
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'Missing mobile number or OTP code.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 405,
        'message' => "$requestMethod Method Not Allowed"
    ]);
}
?>



 

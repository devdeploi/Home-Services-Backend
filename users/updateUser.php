<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods:GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include('userFunction.php');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Send HTTP 200 OK status for preflight requests
    http_response_code(200);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "PUT") {
    // Get the input data from the PUT request
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (!empty($inputData['user_id']) && !empty($inputData['user_name']) && !empty($inputData['mobile_no'])) {
        // Update the user
        $updateUser = updateUser($inputData);
        echo $updateUser;
    } else {
        // If no valid identifier is provided
        $data = [
            'status' => 400,
            'message' => "No valid identifier or required fields provided"
        ];
        echo json_encode($data);
    }
} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod.' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>

<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Return HTTP status 200 OK for preflight requests
    exit();
}
include ('userFunction.php');
$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == "POST"){
    $inputData = json_decode(file_get_contents("php://input"), true);
    if(empty($inputData)){
        $createUser = createUser($_POST);
    }
    else{
        $createUser = createUser($inputData);
    }
    echo $createUser;
}
else
{
    $data = [
        'status' => 405,
        'message' => $requestMethod.' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>

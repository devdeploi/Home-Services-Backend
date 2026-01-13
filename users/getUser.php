<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include('userFunction.php');
$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == "GET"){
    $userList = getUserList();
    // echo $userList;
}
else
{
    $data = [
        'status' => 405,
        'message' => $requestMethod.'Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed ");
    echo json_encode($data);
}

?>
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Send HTTP 200 OK status for preflight requests
    http_response_code(200);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = 'SELECT * FROM serv_type';
    $query_run = mysqli_query($conn, $sql);

    if($query_run){
        if(mysqli_num_rows($query_run) > 0 ){

            $res= [];
            while($row = mysqli_fetch_assoc($query_run)){
                $res[]= $row; 
            }
            $data = [
                'status' => 200,
                'message' => 'Service List fetched successfully!',
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Services Not Found'
            ];
            header("HTTP/1.0 404 No Service");
            echo json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error: Query failed'
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
    }
} else {
    $data = [
        'status' => 405,
        'message' => $_SERVER["REQUEST_METHOD"] . ' method not allowed'
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);


        }
    
?>

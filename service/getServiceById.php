<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';


if($_SERVER['REQUEST_METHOD'] == 'GET') {

   
    if(isset($_GET['serv_id'])) {
        $serv_id = mysqli_real_escape_string($conn, $_GET['serv_id']);
        
        // SQL query to fetch the booking by booking_id
        $sql = "SELECT * FROM serv_type WHERE serv_id = '$serv_id'";
        $query_run = mysqli_query($conn, $sql);

        if($query_run) {
            if(mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_assoc($query_run);
                $data = [
                    'status' => 200,
                    'message' => 'Service fetched successfully!',
                    'data' => $res
                ];
                header("HTTP/1.0 200 Success");
                echo json_encode($data);
            } else {
                $data = [
                    'status' => 404,
                    'message' => 'Service Not Found'
                ];
                header("HTTP/1.0 404 Not Found");
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
            'status' => 400,
            'message' => 'serv_id parameter is required'
        ];
        header("HTTP/1.0 400 Bad Request");
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

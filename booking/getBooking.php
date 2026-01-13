<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';

if($_SERVER['REQUEST_METHOD'] == 'GET') {
     $sql = 'SELECT * FROM serv_booking';
     $sql = 'SELECT serv_booking.*, serv_type.serv_name 
     FROM serv_booking 
     JOIN serv_type ON serv_booking.serv_id = serv_type.serv_id';  
     // JOIN to get serv_name
    $sql = 'SELECT serv_booking.*, serv_type.serv_name, serv_user.user_name , serv_user.mobile_no
    FROM serv_booking 
    JOIN serv_type ON serv_booking.serv_id = serv_type.serv_id
    JOIN serv_user ON serv_booking.user_id = serv_user.user_id';  // JOIN to get user_name

    $query_run = mysqli_query($conn, $sql);

    if($query_run) {
        if(mysqli_num_rows($query_run) > 0) {
            
            $res = [];
            while($row = mysqli_fetch_assoc($query_run)) {
                $res[] = $row;
            }
            // $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC); 
            $data = [
                'status' => 200,
                'message' => 'Booking List fetched successfully!',
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Booking Not Found'
            ];
            header("HTTP/1.0 404 No Booking");
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

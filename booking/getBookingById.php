<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        http_response_code(400); // Send Bad Request response
        echo json_encode(['message' => 'User ID is required']);
        exit;
    }

    // Sanitize user_id
    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

    // SQL query to fetch bookings by user_id
    // $sql = "SELECT * FROM serv_booking WHERE user_id = '$user_id'";
    $sql = "SELECT serv_booking.*, serv_type.serv_name , serv_type.image_url 
     FROM serv_booking 
     JOIN serv_type ON serv_booking.serv_id = serv_type.serv_id
      WHERE serv_booking.user_id = '$user_id'";

    $query_run = mysqli_query($conn, $sql);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $bookings = [];
            while ($res = mysqli_fetch_assoc($query_run)) {
                $bookings[] = $res;
            }
            $data = [
                'status' => 200,
                'message' => 'Bookings fetched successfully!',
                'data' => $bookings
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No bookings found for this user'
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
        'status' => 405,
        'message' => $_SERVER["REQUEST_METHOD"] . ' method not allowed'
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>
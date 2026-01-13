<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: Application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS"); // Fixed typo and added OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

$requestedMethod = $_SERVER["REQUEST_METHOD"];
if ($requestedMethod == 'DELETE') {
    // Get booking_id from query parameter
    $booking_id = isset($_GET['booking_id']) ? mysqli_real_escape_string($conn, $_GET['booking_id']) : null;

    if ($booking_id) {
        $sql = "DELETE FROM serv_booking WHERE booking_id = '$booking_id'";
        $query_run = mysqli_query($conn, $sql);
        
        if ($query_run) {
            if (mysqli_affected_rows($conn) > 0) {
                http_response_code(200);
                echo json_encode([
                    'status' => 200,
                    'message' => "Booking deleted successfully."
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 404,
                    'message' => "No booking found."
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'message' => 'Failed to delete booking.'
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'Booking ID is required.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 405,
        'message' => $requestedMethod . " method not allowed."
    ]);
}
?>
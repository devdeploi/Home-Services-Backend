<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Return HTTP status 200 OK for preflight requests
    exit();
}

include '../inc/dbcon.php';
include 'bookingFunction.php';



// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputData = json_decode(file_get_contents("php://input"), true);
    
    if (!empty($inputData)) {
        $user_name = mysqli_real_escape_string($conn, $inputData['user_name']);
        $mobile_no = mysqli_real_escape_string($conn, $inputData['mobile_no']);
        $serv_name = mysqli_real_escape_string($conn, $inputData['serv_name']);
        $address = mysqli_real_escape_string($conn, $inputData['address']);
        $town = mysqli_real_escape_string($conn, $inputData['town']);

        // Query to get user_id and serv_id using join
        $query = "SELECT su.user_id, st.serv_id 
                  FROM serv_user su 
                  JOIN serv_type st ON st.serv_name = '$serv_name' 
                  WHERE su.mobile_no = '$mobile_no'";
        
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);

        if ($data) {
            $user_id = $data['user_id'];
            $serv_id = $data['serv_id'];

            $sql = "INSERT INTO serv_booking(serv_id, user_id, booked_date, status, created_at, modified_at, address, town)
                    VALUES ('$serv_id', '$user_id', NOW(), 1, NOW(), NOW(), '$address', '$town')";
            
            if (mysqli_query($conn, $sql)) {
                // Send SMS to user
                $message = "Hello $user_name, your booking for the service '$serv_name' at '$address, $town' has been successfully booked. Our service personnel will contact you shortly to confirm the details and schedule the visit.";
                sendMessage($mobile_no, $message);

                // Send SMS to admin
                $sqlAdmin = "SELECT mobile_no FROM serv_user WHERE role = 2";
                $resultAdmin = mysqli_query($conn, $sqlAdmin);
               
                $admin = mysqli_fetch_assoc($resultAdmin);

                if ($admin) {
                    $adminMobile_no = $admin['mobile_no'];
                    $adminMessage = "User ID: $user_id, Name: $user_name has booked a service: $serv_name.";
                    sendMessage($adminMobile_no, $adminMessage);

                    $response = [
                        'success' => true,
                        'status' => 201,
                        'message' => 'Booking created successfully. Message sent to user and admin.'
                    ];
                    http_response_code(201);

                    echo json_encode($response);
                } else {
                    // No admin found
                    $response = [
                        'status' => 404,
                        'message' => 'Admin not found'
                    ];
                    http_response_code(404);
                    echo json_encode($response);
                }
            } else {
                // Insert booking failed
                $response = [
                    'status' => 500,
                    'message' => 'Internal Server Error: Unable to create booking.' . mysqli_error($conn)
                ];
                http_response_code(500);
                echo json_encode($response);
            }
        } else {
            // User or Service not found
            $response = [
                'status' => 404,
                'message' => 'User or service not found'
            ];
            http_response_code(404);
            echo json_encode($response);
        }
    } else {
        // No data provided
        $response = [
            'status' => 400,
            'message' => 'No data provided'
        ];
        http_response_code(400);
        echo json_encode($response);
    }
} else {
    // Method not allowed
    $response = [
        'status' => 405,
        'message' => $_SERVER["REQUEST_METHOD"] . ' method not allowed'
    ];
    http_response_code(405);
    echo json_encode($response);
    
}

?>

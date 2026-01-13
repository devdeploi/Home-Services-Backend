<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';
include 'bookingFunction.php'; // Include SMS functionality

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "PUT") {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($inputData['booking_id'])) {
        $booking_id = mysqli_real_escape_string($conn, $inputData['booking_id']);
        $serv_id = isset($inputData['serv_id']) ? mysqli_real_escape_string($conn, $inputData['serv_id']) : null;
        $user_id = isset($inputData['user_id']) ? mysqli_real_escape_string($conn, $inputData['user_id']) : null;
        $status = isset($inputData['status']) ? mysqli_real_escape_string($conn, $inputData['status']) : null;

        $updateFields = [];
        if (!empty($serv_id)) $updateFields[] = "serv_id = '$serv_id'";
        if (!empty($user_id)) $updateFields[] = "user_id = '$user_id'";
        if (!empty($status)) $updateFields[] = "status = '$status'";

        if (!empty($updateFields)) {
            $updateFieldsString = implode(", ", $updateFields);
            $sql = "UPDATE serv_booking SET $updateFieldsString WHERE booking_id = '$booking_id'";

            if (mysqli_query($conn, $sql)) {
                if (mysqli_affected_rows($conn) > 0) {
                    // Check if status was updated
                    if (isset($inputData['status'])) {
                        // Get user mobile_no and service name
                        $sqlUser = "SELECT su.mobile_no, su.user_name, st.serv_name 
                                    FROM serv_booking sb 
                                    JOIN serv_user su ON sb.user_id = su.user_id 
                                    JOIN serv_type st ON sb.serv_id = st.serv_id 
                                    WHERE sb.booking_id = '$booking_id'";
                        $result = mysqli_query($conn, $sqlUser);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            $userData = mysqli_fetch_assoc($result);
                            $mobile_no = $userData['mobile_no'];
                            $serv_name = $userData['serv_name'];
                            
                            // Customize message based on status
                            // Map status codes to human-readable text
                            $statusText = [
                                '1' => 'Pending',
                                '2' => 'On Progress',
                                '3' => 'Complete',
                                '4' => 'Cancelled'
                            ];
                            $statusDisplay = isset($statusText[$status]) ? $statusText[$status] : $status;
                            $user_name = $userData['user_name'];
                            $message = "Dear $user_name,\n\nWe would like to inform you that the status of your booking for \"$serv_name\" has been updated to: $statusDisplay.\n\nThank you for choosing our services. If you have any questions or need further assistance, please do not hesitate to contact us.\n\nBest regards,\nGeneral Services Team";
                            sendMessage($mobile_no, $message);
                        }
                    }

                    $data = [
                        'status' => 200,
                        'message' => 'Booking updated successfully'
                    ];
                } else {
                    $data = [
                        'status' => 404,
                        'message' => 'Booking not found'
                    ];
                }
            } else {
                $data = [
                    'status' => 500,
                    'message' => 'Internal Server Error: ' . mysqli_error($conn)
                ];
            }
        } else {
            $data = [
                'status' => 400,
                'message' => 'No fields to update'
            ];
        }
    } else {
        $data = [
            'status' => 400,
            'message' => 'Missing booking_id'
        ];
    }
} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod . ' Method Not Allowed'
    ];
}

echo json_encode($data);
?>
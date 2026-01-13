<?php
// Set headers to allow API usage and JSON content type
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../inc/dbcon.php';

// Get the raw data from the body
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['mobile_no']) && !empty($data['user_password'])) {
    // Sanitize input to avoid SQL injection
    $mobile_no = mysqli_real_escape_string($conn, $data['mobile_no']);
    $entered_password = mysqli_real_escape_string($conn, $data['user_password']);

    // Query to fetch user by mobile_no
    $query = "SELECT * FROM serv_user WHERE mobile_no = '$mobile_no'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $hashed_password = $user['user_password']; // Hashed password stored in the database

        // Verify the entered password with the stored hashed password
        if (password_verify($entered_password, $hashed_password)) {
            // Password is correct, now check if the user is verified
            if ($user['is_verified'] != true) {
                http_response_code(403); // Forbidden
                echo json_encode([
                     "status" => 403,
                    "result" => "false",
                    "message" => "User is not verified. Please verify by entering the OTP sent to your mobile."
                ]);
            } else {
                // User is verified, proceed to login
                $user_data = [
                    "user_id" => $user['user_id'],
                    "user_name" => $user['user_name'],
                    "mobile_no" => $user['mobile_no'],
                    "role" => $user['role'],
                    "is_verified" => $user['is_verified'],
                    "created_at" => $user['created_at'],
                    "modified_at" => $user['modified_at']
                ];
                if ($user['role'] == 2) {
                    // Admin-specific response
                    echo json_encode([
                        "status" => 200,
                        "result" => "true",
                        "is_verified" => true,
                        "message" => "Admin login successful",
                        "data" => $user_data,
                        
                    ]);
                } else {

                echo json_encode([
                    "status" => 200,
                    "result" => "true",
                    "is_verified"=>true,
                    
                    "message" => "Login successful",
                    "data" => $user_data
                ]);
                }
            }
        } else {
            // Password is incorrect
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "result" => "false",
                "message" => "Invalid password"
            ]);
        }
    } else {
        // User not found
        http_response_code(404);
        echo json_encode([
            "status" => 404,
            "result" => "false",
            "message" => "User not found"
        ]);
    }
} else {
    // If mobile_no or password is missing in the request
    http_response_code(400);
    echo json_encode([
        "status" => 400,
        "result" => "false",
        "message" => "Please provide both mobile_no and user_password"
    ]);
}

// Close the database connection
mysqli_close($conn);
?>

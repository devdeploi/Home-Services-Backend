<?php
include '../inc/dbcon.php';
// require '../vendor/autoload.php';  // Ensure the Twilio SDK is loaded
use Twilio\Rest\Client;
require __DIR__ . '/../vendor/autoload.php'; 
date_default_timezone_set('Asia/Kolkata');




function getUserList(){
    global $conn;

    $sql = "SELECT * FROM serv_user";
    $query_run = mysqli_query($conn, $sql);
    
    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = [];
            while($row = mysqli_fetch_assoc($query_run)) {
                $res[] = $row;
            }

            // $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status' => 200,
                'result' => "true",
                'message' => "User list Fetch Successfully",
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);
        

        }else{
            $data = [
                'status' => 404,
                'message' => "No Users Found",
            ];
            header("HTTP/1.0 404 No Users Found");
            echo json_encode($data);
            }

    }
    else
    {
        $data = [
            'status' => 500,
            'message' => "Internal Server Error",
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
    
    }
}
//getUserById
function getUserById($userId) {
    global $conn;

    // Sanitize the user ID to prevent SQL Injection
    $userId = mysqli_real_escape_string($conn, $userId);

    $sql = "SELECT * FROM serv_user WHERE user_id = '$userId'";
    $query_run = mysqli_query($conn, $sql);
    
    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_assoc($query_run);
            $data = [
                'status' => 200,
                'message' => "User details fetched successfully",
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => "User not found",
            ];
            header("HTTP/1.0 404 Not Found");
            echo json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => "Internal Server Error",
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
    }
}


//CreateUser
function createUser($input) {
    
    global $conn;

    // Input data
    $user_name = mysqli_real_escape_string($conn, $input['user_name']);
    $user_password = password_hash($input['user_password'], PASSWORD_BCRYPT);
    $mobile_no = mysqli_real_escape_string($conn, $input['mobile_no']);
    $otp_code = rand(1000, 9999);  // Generate OTP
    $expiration_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Check if a role is provided; default to 1 if not
    $role = isset($input['role']) && in_array($input['role'], [1, 2]) ? $input['role'] : 1;



    // Check if mobile number already exists
    $check_mobile = "SELECT * FROM serv_user WHERE mobile_no = '$mobile_no'";
    $check_run = mysqli_query($conn, $check_mobile);

    if (mysqli_num_rows($check_run) > 0) {
        $data = [
            'status' => 409,
            'message' => "Mobile number already registered"
        ];
        return json_encode($data);
    }
    

    // Insert the new user
    $sql = "INSERT INTO serv_user (user_name, user_password, mobile_no, role, otp_code, expiration_time, is_verified, created_at, modified_at) 
            VALUES ('$user_name', '$user_password', '$mobile_no', '$role','$otp_code', '$expiration_time', 0, NOW(), NOW())";
    
    $query_run = mysqli_query($conn, $sql);

    if ($query_run) {
        // Send OTP after user creation
        $messageSid = sendOtp($mobile_no, $otp_code);
        if ($messageSid) {
        // User created, OTP is sent
        $data = [
            'status' => 201,
            'message' => "User created successfully. OTP sent to $mobile_no",
            'otp_code' => $otp_code,  
            'message_sid' => $messageSid,
            'role' => $role
        ];
        header("HTTP/1.0 201 Created");
        return json_encode($data);
    }else{
        // OTP failed to send
        $data = [
            // 'status' => 500,
            'status' => 200,
            'message' => "User created, but failed to send OTP",
            'role' => $role
        ];
    }
    return json_encode($data);
    } else {
        $data = [
            'status' => 500,
            'message' => "Internal Server Error: Unable to create user",
        ];
        return json_encode($data);
    }
}
// Function to send OTP
function sendOtp($mobile_no, $otp) {

    global $conn;
    $created_at = date('Y-m-d H:i:s');

    $expiration_time = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Set expiration time to 15 minutes

    // Update OTP and expiration time in the database
    $sql = "UPDATE serv_user SET otp_code = '$otp', expiration_time = '$expiration_time', created_at='$created_at' WHERE mobile_no = '$mobile_no'";

    
    if (mysqli_query($conn, $sql)) {
    // $account_sid = 'ACccff51d8341c4a67233be225c273e54c';
    // $auth_token = 'db6d26653797e6abd7cabd6ef1e25609';
    // $twilio_number = '+12495460816';    
    
    $account_sid = 'ACf925523fa759276503aa19b35e709fb4';
    $auth_token = '270537a1956b026ca28884c54705d6c8';
    $twilio_number = '+19787880275';    

    $twilio = new Client($account_sid, $auth_token);

    try {
        $message = $twilio->messages->create(
            $mobile_no,
            [
                'from' => $twilio_number, // From (your Twilio number)
                'body' => "Your OTP from General-Service : $otp"
            ]
        );
        return $message->sid;
    } catch (Exception $e) {
        // return false;
        echo "Error: " . $e->getMessage();
        return false;
    }
}
}


function verifyOtp($mobile_no, $otp_code) {
    global $conn;
    $expiration_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    // Fetch OTP and expiration time for the user
    $query = "SELECT otp_code, expiration_time FROM serv_user WHERE mobile_no = '$mobile_no'";
    // $query = "SELECT otp_code, expiration_time FROM serv_user WHERE mobile_no = '$mobile_no' AND otp_code = '$otp_code'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
  error_log("Mobile No: " . $mobile_no);
    error_log("OTP Code: " . $otp_code);
    
    error_log("Stored OTP: " . $user['otp_code'] . " | Entered OTP: " . $otp_code);


    if ($user) {
        // Check if OTP is correct
        if ($user['otp_code'] == $otp_code) {
            // Check if OTP has expired
            $current_time = date('Y-m-d H:i:s');
            if ($current_time <= $user['expiration_time']) {
                // OTP is valid, proceed with verification
                $update = "UPDATE serv_user SET is_verified = 1 WHERE mobile_no = '$mobile_no'";
                mysqli_query($conn, $update);
                http_response_code(200);  
                $data = [
                    'status' => 200,
                    'message' => "OTP verified successfully"
                ];
                return json_encode($data);
                exit;
            } else {
                // OTP expired

                http_response_code(400);
                $data = [

                    'status' => 400,
                    'message' => "OTP expired"
                ];
                return json_encode($data);
            }
        } else {
            // Invalid OTP
            http_response_code(400);
            $data = [
                'status' => 400,
                'message' => "Invalid OTP"
            ];
            return json_encode($data);
        }
    } else {
        // Mobile number not found
        http_response_code(404);
        $data = [
            'status' => 404,
            'message' => "Mobile number not found"
        ];
        return json_encode($data);
    }
}


function resendOtp($mobile_no) {
    global $conn;

    $newOtp = rand(1000, 9999);
    $expiration_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Update OTP and expiration time in the database
    $sql = "UPDATE serv_user SET otp_code = '$newOtp', expiration_time = '$expiration_time' WHERE mobile_no = '$mobile_no'";
    if (mysqli_query($conn, $sql)) {
        return sendOtp($mobile_no, $newOtp); // Reuse sendOtp to send the OTP
    } else {
        error_log("MySQL Error: " . mysqli_error($conn));
        return false;
    }
}


function updateUser($inputData) {
    global $conn;

    
    if (empty($inputData['user_id']) || empty($inputData['user_name']) || empty($inputData['mobile_no'])) {
        return json_encode([
            'status' => 400,
            'message' => 'Missing required fields'
        ]);
    }

    $user_id = mysqli_real_escape_string($conn, $inputData['user_id']);
    $user_name = mysqli_real_escape_string($conn, $inputData['user_name']);
    $mobile_no = mysqli_real_escape_string($conn, $inputData['mobile_no']);
    
    // Prepare SQL update statement
    $sql = "UPDATE serv_user SET user_name = '$user_name', mobile_no = '$mobile_no' WHERE user_id = '$user_id'";
    error_log("Executing query: $sql"); 
    
    if (mysqli_query($conn, $sql)) {
        $affected_rows = mysqli_affected_rows($conn);
        error_log("Affected rows: $affected_rows");

        if ($affected_rows > 0) {
            return json_encode([
                'status' => 200,
                'message' => 'User updated successfully'
            ]);
        } else {
            // Check if the user ID exists before reporting 'User not found'
            $checkUserSql = "SELECT * FROM serv_user WHERE user_id = '$user_id'";
            $result = mysqli_query($conn, $checkUserSql);

            if (mysqli_num_rows($result) > 0) {
                // If user ID exists but update didn't affect any rows
                return json_encode([
                    'status' => 500,
                    'message' => 'User found but no changes made'
                ]);
            } else {
                // User ID does not exist in the database
                return json_encode([
                    'status' => 404,
                    'message' => 'User not found'
                ]);
            }
        }
    } else {
        return json_encode([
            'status' => 500,
            'message' => 'Error updating user: ' . mysqli_error($conn)
        ]);
    }
}
?>












<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include('userFunction.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "DELETE") {

    $inputData = json_decode(file_get_contents("php://input"), true);
    
    if (isset($inputData['mobile_no']) || isset($inputData['user_id'])) {
        // which field to use for deletion
        $mobile_no = isset($inputData['mobile_no']) ? mysqli_real_escape_string($conn, $inputData['mobile_no']) : null;
        $user_id = isset($inputData['user_id']) ? intval($inputData['user_id']) : null;

        // Build the SQL query
        if ($mobile_no) {
            $sql = "DELETE FROM serv_user WHERE mobile_no = '$mobile_no'";
        } elseif ($user_id) {
            $sql = "DELETE FROM serv_user WHERE user_id = '$user_id'";
        } else {
            $data = [
                'status' => 400,
                'message' => "No valid identifier provided"
            ];
            echo json_encode($data);
            exit();
        }

        // Execute the query
        $query_run = mysqli_query($conn, $sql);

        if ($query_run) {
            $data = [
                'status' => 200,
                'message' => "User deleted successfully"
            ];
            echo json_encode($data);
        } else {
            $data = [
                'status' => 500,
                'message' => "Internal Server Error: Unable to delete user"
            ];
            echo json_encode($data);
        }
    } else {
        $data = [
            'status' => 400,
            'message' => "Invalid input data"
        ];
        echo json_encode($data);
    }
} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod . ' Method Not Allowed'
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>

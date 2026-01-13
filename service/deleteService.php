<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../inc/dbcon.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

 if ($requestMethod == 'DELETE') {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($inputData['serv_id'])) {

        $serv_id = mysqli_real_escape_string($conn, $inputData['serv_id']);

        $sql = "DELETE FROM serv_type WHERE serv_id = '$serv_id'";
        $query_run = mysqli_query($conn, $sql);

        if ($query_run) {
            if (mysqli_affected_rows($conn) > 0) {
                $data = [
                    'status' => 200,
                    'message' => "Service with ID $serv_id deleted successfully."
                ];
                echo json_encode($data);
            } else {
                $data = [
                    'status' => 404,
                    'message' => "No service found with ID $serv_id."
                ];
                echo json_encode($data);
            }
        } else {
            $data = [
                'status' => 500,
                'message' => 'Internal server error: ' . mysqli_error($conn)
            ];
            echo json_encode($data);
        }
    } else {
        // If serv_id is not provided
        $data = [
            'status' => 400,
            'message' => 'Service ID is required.'
        ];
        echo json_encode($data);
    }
} else {
    $data = [
        'status' => 405,
        'message' => $_SERVER["REQUEST_METHOD"] . " method not allowed."
    ];
    echo json_encode($data);
}
?>

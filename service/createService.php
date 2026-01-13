<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../inc/dbcon.php';

//  target directory on the server 
//  $targetDir = "D:/New folder/MyServiceApp/src/assets/";
 
$targetDir = "./../assets/";
 $baseUrl = "https://safprotech.com/generalservices/assets/";
// $baseUrl = "http://localhost/generalservices/assets/";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($_POST['serv_name']) && !empty($_POST['serv_name']) && isset($_FILES['image'])) {
        $serv_name = mysqli_real_escape_string($conn, $_POST['serv_name']);
        $image = $_FILES['image']['name'];
        $tempName = $_FILES["image"]["tmp_name"];

        // File type and naming
        $imgExt = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imgExt, $allowedTypes)) {
            $targetFilePath = $targetDir . $image;
            $filelocation = $baseUrl . $image; // URL for accessing the uploaded file
          
            if (file_exists($targetDir . $image)) {
        
            echo json_encode([
                'status' => 409,
                'message' => 'Filename already exists',
                'filename' => $image
            ]);
            exit;
        }
            


            // Move the file to the specified directory
            if (move_uploaded_file($tempName, $targetFilePath)) {
              
                // Insert into the database
                $sql = "INSERT INTO serv_type (serv_name, image_url) VALUES ('$serv_name', '$filelocation')";
                if (mysqli_query($conn, $sql)) {
                    $serv_id = mysqli_insert_id($conn);
                    $data = [
                        'status' => 200,
                        'message' => 'Service Created Successfully',
                        'serv_id' => $serv_id,
                        'serv_name' => $serv_name,
                        'image_url' => $filelocation,
                        
                    ];
                    echo json_encode($data);
                } else {
                    echo json_encode([
                        'status' => 500,
                        'message' => 'Internal Server Error: ' . mysqli_error($conn)
                    ]);
                }
            } else {
                // Log error if file upload fails
                error_log("move_uploaded_file failed for $targetFilePath");
                echo json_encode([
                    'status' => 500,
                    'message' => 'Failed to upload image'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 400,
                'message' => 'Invalid file type. Allowed types: jpg, jpeg, png, gif'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 400,
            'message' => 'Service name and image file are required'
        ]);
    }
} else {
    echo json_encode([
        'status' => 405,
        'message' => $_SERVER["REQUEST_METHOD"] . ' Method Not Allowed'
    ]);
}
?>

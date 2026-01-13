 <?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../inc/dbcon.php';

// Base URL for image location
//   $targetDir = "D:/New folder/MyServiceApp/src/assets/";
//   $baseUrl = "D:/New folder/MyServiceApp/src/assets/";
// //    $baseUrl = "http://localhost/php_General_Service/src/assets/";
 
$targetDir = "./../assets/";
$baseUrl = "https://safprotech.com/generalservices/assets/";


if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (isset($_POST['serv_id']) && (!empty($_POST['serv_name']) || isset($_FILES['image']))) {

        $serv_id = mysqli_real_escape_string($conn, $_POST['serv_id']);
        $updates = [];

        // Update service name if provided
        if (!empty($_POST['serv_name'])) {
            $serv_name = mysqli_real_escape_string($conn, $_POST['serv_name']);
            $updates[] = "serv_name = '$serv_name'";
        }

        // Handle image upload if provided
        if (isset($_FILES['image'])) {
            $image = $_FILES['image']['name'];
            $tempName = $_FILES['image']['tmp_name'];
            $imgExt = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $filenameWithoutExt = pathinfo($image, PATHINFO_FILENAME);
// Debugging log for file name and extension
// error_log("Original File Name: $image");
// error_log("Extracted Extension: $imgExt");
// error_log("File Name Without Extension: $filenameWithoutExt");

            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            // Check if the file type is allowed
            if (in_array($imgExt, $allowedTypes)) {
                // Check if the file already exists
                $targetFilePath = $targetDir . $image;

                // If the file exists, rename it by appending the timestamp
                if (file_exists($targetFilePath)) {
                    
                    // Respond that the file name already existed and was renamed
                    echo json_encode([
                        'status' => 409,
                        'message' => "Filename already exists."
                    ]);
                    exit;
                } else {
                    $filePath = "$baseUrl$image";

                }
// Debugging log for file path
// error_log("New File Name: $newFileName");
// error_log("Target File Path: $targetFilePath");
// error_log("File Path for Response: $filePath");

                // Attempt to move the uploaded file
                if (move_uploaded_file($tempName, $targetFilePath)) {
                    // Add the file path to the updates array
                    $updates[] = "image_url = '$filePath'";
                } else {
                    echo json_encode([
                        'status' => 500,
                        'message' => 'Failed to upload the image.'
                    ]);
                    exit;
                }
            } else {
                echo json_encode([
                    'status' => 400,
                    'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.'
                ]);
                exit;
            }
        }

        // Check if there is anything to update
        if (!empty($updates)) {
            // Update query
            $sql = "UPDATE serv_type SET " . implode(", ", $updates) . " WHERE serv_id = '$serv_id'";

            // Execute the query
            if (mysqli_query($conn, $sql)) {
                if (mysqli_affected_rows($conn) > 0) {
                   // Prepare the success response
            $response = [
                'status' => 200,
                'message' => 'Service updated successfully.'
            ];

            // Include updated fields in the response
            if (!empty($serv_name)) {
                $response['serv_name'] = $serv_name;
            }
            if (!empty($filePath)) {
                $response['image_url'] = $filePath;
            }

            echo json_encode($response);
                   
                } else {
                    echo json_encode([
                        'status' => 404,
                        'message' => 'No changes detected or invalid service ID.'
                    ]);
                }
            } else {
                echo json_encode([
  
                    'status' => 500,
                    'message' => 'Database error: ' . mysqli_error($conn)
                ]);
            }
        } else {
            echo json_encode([
                'status' => 400,
                'message' => 'No valid fields provided for update.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 400,
            'message' => 'Service ID is required, along with either serv_name or an image.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 405,
        'message' => 'Method not allowed. Use POST.'
    ]);
}
?>
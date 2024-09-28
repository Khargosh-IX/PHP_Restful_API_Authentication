<!-- For Validation -->

<?php 

header("Access-Control_Allow_origin: *");
header("Access-Control_Allow_Headers: access");
header("Access-Control_Allow_Methods: GET");
header("Content-Type: application/json; charset-UTF-8");
header("Access-Control_Allow_Headers: Content-Type, Access-Control-Allow_Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/AuthMiddleware.php';

// Fetching all headers
$allHeader = getallheaders();

// Database connection
$db_connection = new Database();
$conn = $db_connection->dbConnection();

// Instantiate Auth with the connection and headers
$auth = new Auth($conn, $allHeader);

// Output the validation result
echo json_encode($auth->isValid());

?>
<?php 

header("Access-Control_Allow_origin: *");
header("Access-Control_Allow_Headers: access");
header("Access-Control_Allow_Methods: POST");
header("Content-Type: application/json; charset-UTF-8");
header("Access-Control_Allow_Headers: Content-Type, Access-Control-Allow_Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/classes/JwtHandler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

function msg($success, $status, $message, $extra = []){
    return array(['succes' => $success, 'status' => $status, 'message => $message'], $extra);
}

// Data from on request

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// If request menthod is not POST

if ($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0, 404, 'Page Not Found!');

// Checking empty fields
elseif(!isset($data->email) ||
    !isset($data->password) ||
    empty(trim($data->email)) ||
    empty(trim($data->password))):

    $fields = ['files' => ['email', 'password']];
    $returnData = msg(0, 422, 'Please fill in all required fileds!', $fields);

//  If there are no empty fields
else:
    $email = trim($data->email);
    $password = trim($data->password);

    // Checking the email format(if invalid format)
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0, 422, 'Invalid email address');
    
    // If password is less then 8 characters
    elseif(strlen($password) < 8):
        $returnData = msg(0, 422, 'Your password must be 8 characters long!');
    
    // The user is abel to perform the login action
    else:
        try{
            $fetch_user_by_email = "SELECT * FROM `users` WHERE `email`=:email";
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $query_stmt->execute();

            // If the user is found by email
            if($query_stmt->rowCount()):
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $check_password = password_verify($password, $row['password']);
            
            // Verify the password, if password is correct then send the login token
            if($check_password):
                $jwt = new JwtHandler();
                $token = $jwt->jwtEncodeData('http://localhost/php_auth_api/', array("user_id"=> $row['id']));

                $returnData = ['succes' => 1, 'message => You have successfully loged in!', 'token'=> $token];

            // If invalid password
            else:
                $returnData = msg(0, 422, 'Invalid Password!');
            endif;
        
        // If user is not found by email
        else:
            $returnData = msg(0, 422, 'Invalid email address!');
        endif;
        }catch(PDOException $e){
            $returnData =msg(0, 500, $e->getMessage());
        }
    endif;

endif;

echo json_encode($returnData);

?>
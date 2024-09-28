<?php 

header("Access-Control_Allow_origin: *");
header("Access-Control_Allow_Headers: access");
header("Access-Control_Allow_Methods: POST");
header("Content-Type: application/json; charset-UTF-8");
header("Access-Control_Allow_Headers: Content-Type, Access-Control-Allow_Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

function msg($success, $status, $message, $extra = []){
    return array(['succes' => $success, 'status' => $status, 'message => $message'], $extra);
}

// Data from on request

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0, 404, 'Page Not Found!');
elseif(!isset($data->name) ||
    !isset($data->email) ||
    !isset($data->password) ||
    empty(trim($data->name)) ||
    empty(trim($data->email)) ||
    empty(trim($data->password))):

    $fields = ['files' => ['name', 'email', 'password']];
    $returnData = msg(0, 422, 'Please fill in all required fileds!', $fields);

//  If there are no empty fields
else:
    $name = trim($data->name);
    $email = trim($data->email);
    $password = trim($data->password);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0, 422, 'Invalid email address');
    elseif(strlen($password) < 8):
        $returnData = msg(0, 422, 'Your password must be 8 characters long!');
        
    elseif(strlen($name) < 2):
        $returnData = msg(0, 422, 'Your name must be 2 characters long!');
    else:
        try{
           $check_email = "SELECT `email` FROM `users` WHERE `email` = :email";
           $check_email_stmt = $conn->prepare($check_email);
           $check_email_stmt ->bindValue(':email', $email, PDO::PARAM_STR);
           $check_email_stmt->execute();

           if($check_email_stmt->rowCount()):
                $returnData = msg(0, 422, 'This email is already taken!');
           else:
            $insert_query = "INSERT INTO `users` (`name`, `email`, `password`) VALUES (:name, :email, :password)";

            $insert_stmt = $conn->prepare($insert_query);

            // Data Binding
            $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR) ;
            $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR) ;
            $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR) ;

            $insert_stmt->execute();
            $returnData = msg(1, 201, 'Register Success!');
           endif;
        }   
        catch (PDOException $e){
            $returnData = msg(0, 0, $e->getMessage());
        }
    endif;
endif;

echo json_encode($returnData);

?>
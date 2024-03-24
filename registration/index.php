<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:3000");

$connection = mysqli_connect("127.0.0.1", "root", "", "dbform");
if (!$connection) {
    /*
    echo "Ошибка: невозможно установить соединение с MySQL " . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Код ошибки error: " . mysqli_connect_error() . PHP_EOL;
    */
    send_response(0, "An error occurred");
}

$form_data = file_get_contents('php://input');

// Распарсит JSON строку в ассоциативный массив
$data = json_decode($form_data, true);

$name = $data['name'];
$surname = $data['surname'];
$email = $data['email'];
$login = $data['login'];
$password = $data['password'];
$source = $data['source'];
$gender = $data['gender'];

if(exists_user_by_login($connection, $login) == true) {
    send_response(2, "This login is already taken");
}

if(exists_user_by_email($connection, $email) == true) {
    send_response(3, "This email is already taken");
}

register_user($connection, $name, $surname, $email, $login, $password, $gender);

function send_response($status, $message) {
    $response = array(
        'status' => $status,
        'message' => $message
    );

    $json_response = json_encode($response);
    echo $json_response;
    //$connection.close
    exit;
}

function exists_user_by_login($connection, $login) {
    $stmt = $connection->prepare("CALL check_user_by_login (?, @param_out)");
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $select = $connection->query("SELECT @param_out");
    $result = $select->fetch_assoc();
    $param_out = $result['@param_out'];

    $stmt->close();

    return $param_out;
}

function exists_user_by_email($connection, $email) {
    $stmt = $connection->prepare("CALL check_user_by_email (?, @param_out)");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $select = $connection->query("SELECT @param_out");
    $result = $select->fetch_assoc();
    $param_out = $result['@param_out'];

    $stmt->close();

    return $param_out;
}

function register_user($connection, $name, $surname, $email, $login, $password, $gender) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $idgender = 0;
    if ($gender == "f")
        $idgender = 1;

    $token = generate_token($login);
    $query = "CALL register_user('$name', '$surname', '$email', '$login', '$hashed_password', $idgender, '$token')";
    $result = mysqli_query($connection, $query);

    if(!$result) {
        send_response(0, "An error occurred");
    }

    if(exists_user_by_login($connection, $login) == true) {
        send_response(1, "Register success!");
    }
    else {
        send_response(0, "An error occurred");
    }
}

function generate_token($login) {
    $token = bin2hex(random_bytes(32));

    $separator = ":"; 
    $tokenData = $token . $separator . $login;
    
    //$hashedTokenData = hash('sha256', $tokenData);


    return $tokenData;
}

/*
$user_exists = $connection->query("SELECT exists_user_by_login($user_login)");
echo $user_exists;
if($user_exists === TRUE) {
    echo "Пользователь $user_login существует";
}
else {
    echo "Пользователь $user_login не существует";
}
*/

?>
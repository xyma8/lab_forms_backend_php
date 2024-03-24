<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:3000");

$connection = mysqli_connect("127.0.0.1", "root", "", "dbform");

if (!$connection) {
    send_response(0, "An error occurred");
}

$form_data = file_get_contents('php://input');

$data = json_decode($form_data, true);

$login = $data['login'];
$password = $data['password'];

if(exists_user_by_login($connection, $login) == false) {
    send_response(2, "This login dont exists");
}

login_user($connection, $login, $password);


function login_user($connection, $login, $password) {
    $hashed_password = get_hash_password($connection, $login);

    if (password_verify($password, $hashed_password)) {
        // Пароль совпадает
        $token = get_token($connection, $login);
        send_response(1, array(
            'message' => "Login success",
            'token' => $token
        ));
    } else {
        send_response(3, "Password incorrect");
    }
}

function get_hash_password($connection, $login) {
    $stmt = $connection->prepare("CALL get_hash_password_by_login (?, @param_out)");
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $select = $connection->query("SELECT @param_out");
    $result = $select->fetch_assoc();
    $param_out = $result['@param_out'];

    $stmt->close();

    return $param_out;
}

function get_token($connection, $login) {
    $stmt = $connection->prepare("CALL get_token_by_login (?, @param_out)");
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $select = $connection->query("SELECT @param_out");
    $result = $select->fetch_assoc();
    $param_out = $result['@param_out'];

    $stmt->close();

    return $param_out;
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

function get_any_param_by_login($stmt) {
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $select = $connection->query("SELECT @param_out");
    $result = $select->fetch_assoc();
    $param_out = $result['@param_out'];

    $stmt->close();

    return $param_out;
}


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
?>
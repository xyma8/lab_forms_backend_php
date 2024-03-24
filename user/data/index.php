<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:3000");

$connection = mysqli_connect("127.0.0.1", "root", "", "dbform");

if (!$connection) {
    send_response(0, "An error occurred");
}

$form_data = file_get_contents('php://input');
$data = json_decode($form_data, true);

$token = $data['token'];

if (empty($token)) {
    send_response(2, "Token dont exists");
}

send_data($connection, $token);

function send_data($connection, $token) {
    $login = get_login_by_token($connection, $token);

    if (empty($login)) {
        send_response(0, "User with this token dont exists");
    }

    send_response(1, array(
        'message' => "Success",
        'login' => $login
    ));
}

function get_login_by_token($connection, $token) {
    $stmt = $connection->prepare("CALL get_login_by_token (?, @param_out)");
    $stmt->bind_param("s", $token);
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
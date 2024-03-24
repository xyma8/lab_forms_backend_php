<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:3000");

$connection = mysqli_connect("127.0.0.1", "root", "", "dbform");

if (!$connection) {
    send_response(0, "An error occurred");
}

$form_data = file_get_contents('php://input');

// Распарсит JSON строку в ассоциативный массив
$data = json_decode($form_data, true);

$code = $data['code'];
$login = $data['login'];

if($code==1) {
    $theme = getTheme($connection, $login);

    if($theme == 0) {
        send_response(0, "Light theme");
    }
    elseif($theme==1) {
        send_response(1, "Dark theme");
    }
    else {
        send_response(2, "An error occurred");
    }
}

if($code==2) {
    changeTheme($connection, $login);
    send_response(1, "Theme changed");
}

function getTheme($connection, $login) {
    $stmt = $connection->prepare("CALL get_current_theme (?, @param_out)");
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $select = $connection->query("SELECT @param_out");
    $result = $select->fetch_assoc();
    $param_out = $result['@param_out'];

    $stmt->close();

    return $param_out;
}

function changeTheme($connection, $login) {
    $query = "CALL change_dark_theme('$login')";
    $result = mysqli_query($connection, $query);

    if(!$result) {
        send_response(0, "An error occurred");
    }
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
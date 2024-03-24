<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:3000");

$secretKey = '6LfLkJUpAAAAACH38olngW2b1rTUpA15QYMFjcn7';
$recaptchaResponse = $_POST['g-recaptcha-response'];

// URL проверки reCAPTCHA
$url = 'https://www.google.com/recaptcha/api/siteverify';

// Параметры запроса
$params = [
    'secret' => $secretKey,
    'response' => $recaptchaResponse,
];

// Отправляем запрос
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

if ($responseData['success']) {
    // reCAPTCHA пройдена
    echo 'OK';
} else {
    // reCAPTCHA не пройдена
    echo 'ERROR';
    // Вы можете вывести сообщение об ошибке
    echo $responseData['error-codes'][0];
}

?>
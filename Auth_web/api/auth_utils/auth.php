<?php
//TODO безопасные заголовки https://only-to-top.ru/blog/programming/2019-06-20-registraciya-i-avtorizaciya-v-php-s-jwt.html, https://habr.com/ru/post/502702/
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';

$error_code = 0;
$message = '';
$database = new DBManager();
try {
    $user_data = get_http_data();
    if (isset($user_data['purpose'])) {
        if ($database->connect()) {

            if ($user_data['purpose'] == 'logout')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/auth_utils/auth/logout.php';
            else if ($user_data['purpose'] == 'login')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/auth_utils/auth/login.php';

            else {
                $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
            }
        } else {
            $error_code = CodesAndMessages::DB_NOT_AVAILABLE;
        }
    } else {
        $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
    }

} catch (Exception $e) {
    $error_code = CodesAndMessages::SMTH_WRONG;
    $message = $e->getMessage();
}
echo get_answer_for_client($error_code, $message, $database->error_msg, []);


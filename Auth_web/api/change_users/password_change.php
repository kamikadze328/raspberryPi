<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';


$error_code = 0;
$message = '';
$database = new DBManager();
$user_data = get_http_data();
if(isset($user_data['login']) && isset($user_data['oldPassword']) && isset($user_data['newPassword'])) {
    try {
        if ($database->connect()) {
            $user = new User($user_data['login'], $user_data['oldPassword']);
            if ($database->user_exists($user)) {

                include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
                $sec_mng = new SecurityManager();
                if ($sec_mng->check_password($user->password, $user->password_from_db)) {
                    if($database->update_user_password($user->id, $sec_mng->get_secure_password($user_data['newPassword']))){
                        $message = 'Успешно!';
                    } else{
                        $error_code = CodesAndMessages::SMTH_WRONG;
                    }

                } else {
                    $error_code = CodesAndMessages::WRONG_PASSWD;
                }
            } else {
                $error_code = CodesAndMessages::USER_NOT_EXISTS;
            }

        } else {
            $error_code = CodesAndMessages::DB_NOT_AVAILABLE;
        }
    } catch (Exception $e) {
        $error_code = CodesAndMessages::SMTH_WRONG;
        $message = $e->getMessage();
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
echo get_answer_for_client($error_code, $message, $database->error_msg, []);
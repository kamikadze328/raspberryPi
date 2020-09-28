<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';

if (isset($user_data['user_id'])) {
    if (!$database->user_exists($user)) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
        $sec_mng = new SecurityManager();
        if($database->update_user_passwd($user_data['user_id'], $sec_mng->generate_password())){
            $message = 'Успешно!';
            $data['password'] = $user->password;
        } else{
            $error_code = CodesAndMessages::SMTH_WRONG;
        }
        if ($database->create_user($user)) {
            $data['password'] = $user->password;
            $message = "Пользователь был создан.";
        } else {
            $error_code = CodesAndMessages::DB_ERROR;
        }

    } else {
        $error_code = CodesAndMessages::USER_EXISTS;
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}

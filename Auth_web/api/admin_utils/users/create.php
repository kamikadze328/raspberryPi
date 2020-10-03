<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';

if(isset($user_data['login'])) {
    $sec_mng = new SecurityManager();
    $user = new User($user_data['login'], $sec_mng->generate_password());
    if (!$database->user_exists($user)) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
        $sec_mng = new SecurityManager();
        $sec_mng->set_secure_password($user);

        if(isset($user_data['description']))
            $user->description = $user_data['description'];

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

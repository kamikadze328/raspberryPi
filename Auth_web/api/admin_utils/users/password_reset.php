<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if (isset($user_data['user_id']) && isset($user_data['login'])) {
    $user = new User($user_data['login'], $sec_mng->generate_password());
    if ($db->user_exists($user) && $user->id == intval($user_data['user_id'])) {
        $sec_mng->set_secure_password($user);

        if ($db->update_user_passwd($user)) {
            $message = 'Успешно изменён пароль!';
            $data['password'] = $user->password;
        } else {
            $error_code = CodesAndMessages::SMTH_WRONG;
        }

    } else {
        $error_code = CodesAndMessages::USER_NOT_EXISTS;
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}

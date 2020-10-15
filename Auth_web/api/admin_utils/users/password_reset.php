<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_man = new SecurityManager();

if (isset($user_data['user_id']) && isset($user_data['login'])) {
    $user = new User($user_data['login'], $sec_mng->generate_password());
    if ($db->user_exists($user) && $user->id == intval($user_data['user_id'])) {
        if (!($sec_man->is_user_admin() && ($user->role_id === SecurityManager::ADMIN_ROLE_ID || $user->role_id === SecurityManager::SUPER_ADMIN_ROLE_ID))) {
            $user->password_secure = $sec_mng->get_secure_password($user->password);

            if ($db->update_user_password($user)) {
                $message = 'Успешно изменён пароль!';
                $data['password'] = $user->password;
            } else {
                $error_code = CodesAndMessages::SMTH_WRONG;
            }
        } else {
            $error_code = CodesAndMessages::CANT_UPDATE_USER;
        }
    } else {
        $error_code = CodesAndMessages::USER_NOT_EXISTS;
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}

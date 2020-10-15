<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_man = new SecurityManager();

if(isset($user_data['login']) && isset($user_data['description']) && isset($user_data['role_id'])) {
    $user = new User($user_data['login'], $sec_mng->generate_password());
    if (!$db->user_exists($user)) {
        $user->role_id = intval($user_data['role_id']);
        if (!($sec_man->is_user_admin() && $user->role_id === SecurityManager::SUPER_ADMIN_ROLE_ID)) {
            $user->password_secure = $sec_mng->get_secure_password($user->password);

            $user->description = $user_data['description'];

            if ($db->create_user($user)) {
                $data['password'] = $user->password;
                $message = "Пользователь был создан.";
            } else {
                $error_code = CodesAndMessages::DB_ERROR;
            }
        } else {
            $error_code = CodesAndMessages::CANT_SET_ROLE;
        }
    } else {
        $error_code = CodesAndMessages::USER_EXISTS;
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}

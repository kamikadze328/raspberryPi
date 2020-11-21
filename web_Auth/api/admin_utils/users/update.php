<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_man = new SecurityManager();

if (isset($user_data['user_id']) && isset($user_data['login']) && isset($user_data['new_role_id']) && isset($user_data['new_login'])) {
    $user = new User($user_data['login'], '');
    $user_id = intval($user_data['user_id']);
    $new_role_id = intval($user_data['new_role_id']);
    $new_login = $user_data['new_login'];

    if ($db->user_exists($user) && $user->id == $user_id) {
        if (!($sec_man->is_user_admin() && ($user->role_id === SecurityManager::ADMIN_ROLE_ID || $user->role_id === SecurityManager::SUPER_ADMIN_ROLE_ID))) {
            if (!($sec_man->is_user_admin() && $new_role_id === SecurityManager::SUPER_ADMIN_ROLE_ID)) {

                $result = $user->role_id === $new_role_id && $user->login === $new_login ? true : $db->update_user_by_id($user_id, $new_role_id, $new_login);
                if ($result)
                    $message = 'Успешно изменен пользователь!';
                else {
                    $error_code = CodesAndMessages::DB_ERROR;
                }
            } else {
                $error_code = CodesAndMessages::CANT_SET_ROLE;
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

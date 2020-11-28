<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_man = new SecurityManager();

if(isset($user_data['user_id'])) {
    $user = new User('', '');
    $user->id = $user_data['user_id'];
    if ($db->user_exists_by_id($user)) {
        if (!($sec_man->is_user_admin() && ($user->role_id === SecurityManager::ADMIN_ROLE_ID || $user->role_id === SecurityManager::SUPER_ADMIN_ROLE_ID))) {
            if ($db->delete_user($user_data['user_id'])) {
                $message = 'Успешно удалён пользователь!';
            } else {
                $error_code = CodesAndMessages::DB_ERROR;
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
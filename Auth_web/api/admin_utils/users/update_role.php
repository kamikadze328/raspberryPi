<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if (isset($user_data['user_id']) && isset($user_data['login']) && isset($user_data['role_id'])) {
    $user = new User($user_data['login'], '');
    $user_id = intval($user_data['user_id']);
    $role_id = intval($user_data['role_id']);
    if ($db->user_exists($user) && $user->id == $user_id) {
        $result = $user->role_id !== $role_id ? true : $db->update_user_role_by_id($user_id, $role_id);
        if($result)
            $message = 'Успешно изменена роль!';
        else{
            $error_code = CodesAndMessages::DB_ERROR;
        }
    } else {
        $error_code = CodesAndMessages::USER_NOT_EXISTS;
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}

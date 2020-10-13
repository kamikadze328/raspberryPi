<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if (isset($user_data['role_id']) && isset($user_data['permissions'])) {

    if ($db->update_role($user_data['role_id'], $user_data['permissions']) !== false) {
        $message = "Роль была обновлена.";
    } else {
        $error_code = CodesAndMessages::DB_ERROR;
    }


} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
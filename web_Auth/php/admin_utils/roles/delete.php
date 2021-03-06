<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';

if (isset($user_data['role_id'])) {
    $role_id = intval($user_data['role_id']);
    if (!in_array($role_id, SecurityManager::DEFAULTS_ROLES)) {
        if ($db->delete_role($role_id) !== false) {
            $message = "Роль была удалена.";
        } else {
            $error_code = CodesAndMessages::DB_ERROR;
        }
    } else {
        $error_code = CodesAndMessages::CANT_UPDATE_ROLE;
    }


} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
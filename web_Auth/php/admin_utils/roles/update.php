<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';

if (isset($user_data['role_id']) && isset($user_data['name']) && isset($user_data['description']) && isset($user_data['permissions'])) {
    $role_id = intval($user_data['role_id']);
    $permissions = json_decode($user_data['permissions'], true);
    if (!in_array($role_id, SecurityManager::DEFAULTS_ROLES)) {
        if ($db->update_role($role_id, $permissions, $user_data['name'], $user_data['description'])) {
            $message = "Роль была обновлена.";
        } else {
            $error_code = CodesAndMessages::DB_ERROR;
        }
    } else {
        $error_code = CodesAndMessages::CANT_UPDATE_ROLE;
    }

} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
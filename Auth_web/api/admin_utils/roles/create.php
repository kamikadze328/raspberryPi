<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if(isset($user_data['name']) && isset($user_data['description']) && isset($user_data['permissions'])) {

    $result = $db->create_role($user_data['name'], $user_data['description'], $user_data['permissions']);

    if ($result !== false) {
        $data['role_id'] = $result;
        $message = "Роль была создана.";
    } else {
        $error_code = CodesAndMessages::DB_ERROR;
    }


} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
<?php

/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if (isset($user_data['role_id'])) {

    if ($db->delete_role($user_data['role_id']) !== false) {
        $message = "Роль была удалена.";
    } else {
        $error_code = CodesAndMessages::DB_ERROR;
    }


} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
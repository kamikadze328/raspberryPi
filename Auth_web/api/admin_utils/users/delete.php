<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if(isset($user_data['user_id'])) {
    if ($database->delete_user($user_data['user_id'])) {
        $message = 'Успешно!';
    } else {
        $error_code = CodesAndMessages::DB_ERROR;
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
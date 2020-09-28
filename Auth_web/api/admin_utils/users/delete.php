<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if($database->delete_user($user_data['user_id'])){
    $message = 'Успешно!';
} else {
    $error_code = CodesAndMessages::DB_ERROR;
}
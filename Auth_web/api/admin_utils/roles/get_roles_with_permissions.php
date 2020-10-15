<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

$data = $db->get_roles_with_permissions();
if ($data === false) {
    $error_code = CodesAndMessages::DB_ERROR;
}



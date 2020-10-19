<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

$data['roles'] = $db->get_roles_with_permissions();
$data['urls'] = $db->get_urls();
if ($data['roles'] === false || $data['urls'] === false) {
    $error_code = CodesAndMessages::DB_ERROR;
}



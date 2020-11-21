<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_mng = new SecurityManager();
$can_write = $sec_mng->can_write_resource($permission_level);
$can_read = $sec_mng->can_read_resource($permission_level);

include_once 'get_file.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/unp/php/LIST_PATHS.php';

if ($can_read) {
    echo json_encode(strpos(get_file($IS_LOCALHOST), 'error') > -1);
} else throw new AccessDeniedException(false, true);


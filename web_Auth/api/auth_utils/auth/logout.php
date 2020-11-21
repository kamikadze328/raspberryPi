<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';

$sec_mng = new SecurityManager();
$token = $sec_mng->get_token();
$database->delete_user_token($token, intval($sec_mng->get_user_id_by_token($token)));
$sec_mng->clear_token_on_client();
header('Location: ' .  '/');



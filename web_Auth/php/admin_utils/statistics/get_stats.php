<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if (isset($user_data['date_min']) && isset($user_data['date_max'])) {
    $data = $db->get_sessions(get_seconds_from_str_ms($user_data['date_min']), get_seconds_from_str_ms($user_data['date_max']));
    if ($data === false) {
        $error_code = CodesAndMessages::DB_ERROR;
    }

} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}

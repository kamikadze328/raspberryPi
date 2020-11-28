<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
$CUR_DIR = $_SERVER['DOCUMENT_ROOT'] . '/graphs/php/';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_mng = new SecurityManager();
$can_write = $sec_mng->can_write_resource($permission_level);
$can_read = $sec_mng->can_read_resource($permission_level);

$error_code = 0;
$message = '';
$data = array();
try {
    if ($can_read) {
        $user_data = get_http_data();
        if (isset($user_data['purpose'])) {
            $purpose = $user_data['purpose'];
            if ($purpose == 'data')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/data/get_data.php';
            else if ($purpose == 'data_dyn')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/data/get_data_dyn.php';
            else if ($purpose == 'data_avg')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/data/get_data_avg.php';
            else
                $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
        } else {
            $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
        }
    } else throw new AccessDeniedException(false, true);
} catch (PDOException $e) {
    $error_code = CodesAndMessages::SMTH_WRONG;
    $message = $e->getMessage();
}
echo get_answer_for_client($error_code, $message, $db->error_msg, $data);
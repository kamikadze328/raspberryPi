<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';

$error_code = 0;
$message = '';
$data = array();
$database = new DBManager();
try {
    $user_data = get_http_data();
    if ($user_data['date_min'] && $user_data['date_max']) {
        if ($database->connect()) {
            $data = $database->get_sessions(get_seconds_from_str_ms($user_data['date_min']), get_seconds_from_str_ms($user_data['date_max']));
            if($data === false){
                $error_code = CodesAndMessages::DB_ERROR;
            }
        } else {
            $error_code = CodesAndMessages::DB_NOT_AVAILABLE;
        }
    } else {
        $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
    }
} catch (Exception $e) {
    $error_code = CodesAndMessages::SMTH_WRONG;
    $message = $e->getMessage();
    //echo $message;
}
echo get_answer_for_client($error_code, $message, $database->error_msg, $data);

function get_seconds_from_str_ms($ms){
    return intdiv(intval($ms), 1000);
}
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';

$error_code = 0;
$message = '';
$data = array();
$database = new DBManager();
try {
    $user_data = get_http_data();

    if (isset($user_data['purpose'])) {
        $purpose = $user_data['purpose'];
        if ($database->connect()) {
            if ($purpose == 'delete')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/delete.php';
            else if ($purpose == 'create')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/create.php';
            else if ($purpose == 'reset')
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/password_reset.php';
            else
                $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
        } else {
            $error_code = CodesAndMessages::DB_NOT_AVAILABLE;
        }
    } else {
        $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
    }

} catch (Exception $e) {
    $error_code = CodesAndMessages::SMTH_WRONG;
    $message = $e->getMessage();
}
echo get_answer_for_client($error_code, $message, $database->error_msg, $data);
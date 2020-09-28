<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';

$error_code = 0;
$message = '';
$data = array();
$database = new DBManager();
try {
    $user_data = get_http_data();
    $path = parse_path();
    if ($path != false) {
        if ($database->connect()) {
            include $_SERVER['DOCUMENT_ROOT'] . '/api/statistics/save_stat.php';
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
//echo get_answer_for_client($error_code, $message, $database->error_msg, $data);

function parse_path()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        preg_match('/^https?:\/\/([^\/]+)([^\?]+)/', $_SERVER['HTTP_REFERER'], $path);
        if (count($path) == 3) {
            $path = $path[2];
            if (substr($path, 0, 6) === '/admin')
                $path = '/admin';
            return $path;
        }
    }
    return false;
}
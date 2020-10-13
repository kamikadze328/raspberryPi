<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/libs/Mobile_Detect.php';

$error_code = 0;
$message = '';
$data = array();
$db = new DBManager();
try {
    $user_data = get_http_data();
    $path = parse_current_path($_SERVER['HTTP_REFERER']);
    if (!is_null($path)) {
        if ($db->connect()) {
            $sec_mng = new SecurityManager();
            $user_id = $sec_mng->is_auth_user() ? $sec_mng->get_user_id_by_token($sec_mng->get_token()) : -1;
            if (!($user_id == -1 && $path == '/')) {

                $device = get_client_device();

                $session_id = $sec_mng->is_session_exists() ? $sec_mng->get_session_id() : null;

                $ip = is_null($session_id) ? ip2int_with_mask(get_client_ip()) : null;
                $session_id = $db->save_user_stat($path, $user_id, $device, time(), $session_id, $ip);
                if ($session_id !== false) $sec_mng->save_session($session_id);
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
}



function get_client_ip()
{
    $ipaddress = null;
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = null;
    return $ipaddress;
}

function ip2int_with_mask($ip)
{
    $mask = '255.255.0.0';
    if(is_null($ip)) return null;
    else {
        $ip_arr = explode('.', long2ip(ip2long($ip) & ip2long($mask)));
        return count($ip_arr) == 4 ? ($ip_arr[0] * 1000 + $ip_arr[1]) : null;
    }
}

function get_client_device(){
    $mobile_detect = new Mobile_Detect;
    $user_agent = null;
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_detect->setUserAgent($user_agent);
    }
    $device = 0;//'PC';
    if ($mobile_detect->isMobile() && !$mobile_detect->isTablet())
        if ($mobile_detect->is('iOS') || $mobile_detect->is('iphone'))
            $device = 11;   //'Phone iOS';
        else if ($mobile_detect->is('Android') || !$mobile_detect->is('iphone'))
            $device = 12;   //'Phone Android';
        else $device = 10;  //'Phone';
    else if ($mobile_detect->isTablet())
        if ($mobile_detect->is('iOS') || $mobile_detect->is('ipad'))
            $device = 21;   //'Tablet iOS';
        else if ($mobile_detect->is('Android'))
            $device = 22;   //'Tablet Android';
        else $device = 20;  //'Tablet';
    return $device;
}
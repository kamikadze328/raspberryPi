<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/libs/Mobile_Detect.php';

//check user_id
$sec_mng = new SecurityManager();
$user_id = $sec_mng->is_auth_user() ? $sec_mng->get_user_id_by_token($sec_mng->get_token()) : -1;


//check device
$mobile_detect = new Mobile_Detect;
$user_agent = null;
if(isset($_SERVER['HTTP_USER_AGENT'])) {
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

$db_mng = new DBManager();
$db_mng->connect();
$session_id = $sec_mng->is_session_exists() ? $sec_mng->get_session_id(): null;
$session_id = $db_mng->save_user_stat($path, $user_id, $device, time(), $session_id);
if($session_id !== false) $sec_mng->save_session($session_id);

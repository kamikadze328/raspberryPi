<?php
include_once 'CodesAndMessages.php';

//$CONFIGS_FILE = '../config/sync_data.conf.json';
$CONFIGS_FILE = $_SERVER['DOCUMENT_ROOT'] . '/config/sync_data.conf.json';
$ERROR_PAGE='/';

function read_config()
{
    global $CONFIGS_FILE;
    return read_json($CONFIGS_FILE);
}

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}

function get_answer_for_client($error_code, $message, $db_message, $data)
{
    $message = $error_code < 300 ? $message : CodesAndMessages::CODE_TO_MESSAGE[$error_code];
    $message = (isset($db_message[0]) && $db_message[0]) ?  '('.$db_message[1].') ' .$message : $message;
    $answer = $error_code > 0 ?
        array(
            "error" => array(
                "code" => $error_code,
                "message" => $message,
                "dbMessage" => $db_message))
        : array(
            "message" => $message,
            "data" => $data,
            "code" => 200);

    return json_encode($answer, JSON_UNESCAPED_UNICODE);
}

function get_http_data(){
    return json_decode(file_get_contents("php://input"), true);
}

function get_seconds_from_str_ms($ms){
    return intdiv(intval($ms), 1000);
}

function parse_current_path($refer)
{
    if (!is_null($refer)) {
        preg_match('/^https?:\/\/([^\/]+)([^\?]+)/', $refer, $path);
        if (count($path) == 3)
            return parse_path($path[2]);
    }
    return null;
}

function parse_path($url){
    if (substr($url, 0, 6) === '/admin')
        $url = '/admin';
    return explode('?', $url)[0];
}

function parse_get_params($url){
    $params = explode('?', $url);
    $return_params = array();
    if(count($params) > 1){
        $params = array_slice($params,1);
        foreach ($params as $p_str){
            $p = explode('=', $p_str);
            $return_params[$p[0]] = $p[1];
        }
    }
    return $return_params;
}
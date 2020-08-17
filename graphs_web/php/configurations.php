<?php
$CONFIGS_FILE = "../configs.json";
function read_config()
{
    global $CONFIGS_FILE;
    if (file_exists($CONFIGS_FILE)) return json_decode(file_get_contents($CONFIGS_FILE), true);
    else return array();
}
function write_config($content){
    global $CONFIGS_FILE;
    return !!file_put_contents($CONFIGS_FILE, $content);
}

$error_message = "internal server error";
$post = json_decode(file_get_contents('php://input'), true);
$answer = null;
$isOK = false;
//write configs
if(isset($post['configs'])){
    $isOK = write_config($post['configs']);
}
//read configs
else{
    $answer = read_config();
    echo json_encode($answer);
    $isOK = true;
}
if (!$isOK) {
    $out["error"] = array("message" => 'Can`t write configs', "answer" => $answer, "request-body" => $post);
    echo json_encode($out);
}
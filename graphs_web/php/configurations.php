<?php
$CUR_DIR = $_SERVER['DOCUMENT_ROOT'].'/graphs/php/';
$CONFIGS_FILE_GRAPH = $_SERVER['DOCUMENT_ROOT']."/graphs/configs.json";

function read_db_config($CONFIGS_FILE_GRAPH)
{
    if (file_exists($CONFIGS_FILE_GRAPH)) return json_decode(file_get_contents($CONFIGS_FILE_GRAPH), true);
    else return array();
}
function write_config($content, $CONFIGS_FILE_GRAPH){
    return !!file_put_contents($CONFIGS_FILE_GRAPH, $content);
}

$error_message = "internal server error";
$post = json_decode(file_get_contents('php://input'), true);
$answer = null;
$isOK = false;
//write configs
if(isset($post['configs'])){
    $isOK = write_config($post['configs'], $CONFIGS_FILE_GRAPH);
}
//read configs
else{

    $answer = read_db_config($CONFIGS_FILE_GRAPH);
    echo json_encode($answer);
    $isOK = true;
}
if (!$isOK) {
    $out["error"] = array("message" => 'Can`t write configs', "answer" => $answer, "request-body" => $post);
    echo json_encode($out);
}
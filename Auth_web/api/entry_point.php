<?php /** @noinspection PhpUndefinedVariableInspection */

include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';

function pass_to_uri(){
    $uri = $_SERVER['REQUEST_URI'];
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $uri))
        include $_SERVER['DOCUMENT_ROOT'] . $uri;
    else
        header('HTTP/1.0 404 Not Found');
}

function check_whitelist($uri){
    return is_path_in_whitelist($uri)
        || (isset($_SERVER['HTTP_REFERER']) && is_path_in_whitelist($_SERVER['HTTP_REFERER']));
}
function is_path_in_whitelist($path){
    return (preg_match('/.*\/info_dev_err\.html/', $path)
        || preg_match('/.*\/info_teg\.html/', $path)
    );
}

$sec_man = new SecurityManager();
$uri = $_SERVER['REQUEST_URI'];

if(check_whitelist($uri)){
    pass_to_uri();
}
else if($sec_man->isset_token() && $sec_man->isset_user_meta()) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';
    $db = new DBManager();
    $db->connect();
    $token = $sec_man->get_token();
    $user_id = intval($sec_man->get_user_id_by_token($token));
    if ($db->is_valid_token($token, $user_id) === false) {
        $sec_man->clear_token_on_client();
        header('HTTP/1.1 401 Unauthorized');
        header('Location: ' . $ERROR_PAGE . '?error=401');
    } else {

        if (preg_match('/.*\.php/', $uri)) {
            pass_to_uri();
        } else if ($uri === '/unp/'
            || $uri === '/syncdata/'
            || $uri === '/graphs/')
            include $_SERVER['DOCUMENT_ROOT'] . $uri . 'index.html';

        else {
            header('HTTP/1.1 404 Not Found');
            header('Location: ' . $ERROR_PAGE . '?error=401');
        }

    }

} else {

    $sec_man->clear_token_on_client();
    header('HTTP/1.1 401 Unauthorized');
    header('Location: '.$ERROR_PAGE . '?error=401');
}
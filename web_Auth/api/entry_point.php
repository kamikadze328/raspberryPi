<?php /** @noinspection PhpUndefinedVariableInspection */

include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/DBManager.php';
foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/api/objects/Exceptions/*.php') as $filename) {
    include $filename;
}


function pass_to_uri()
{
    $uri = $_SERVER['REQUEST_URI'];
    global $db;
    global $permission_level;
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $uri))
        include $_SERVER['DOCUMENT_ROOT'] . $uri;
    else if (file_exists($_SERVER['DOCUMENT_ROOT'] . parse_path($uri)))
        include $_SERVER['DOCUMENT_ROOT'] . parse_path($uri);
    else
        header('HTTP/1.0 404 Not Found');
}

function check_whitelist($uri)
{
    return is_path_in_whitelist($uri)
        || (isset($_SERVER['HTTP_REFERER']) && is_path_in_whitelist($_SERVER['HTTP_REFERER']));
}

function is_path_in_whitelist($path)
{
    return (substr($path, -18) === '/info_dev_err.html'
        || substr($path, -14) === '/info_teg.html'
    );
}

function is_stats ($path){
    return (substr($path, -25) === '/api/statistics/stats.php');
}

$sec_man = new SecurityManager();

$uri = parse_path($_SERVER['REQUEST_URI']);
$db = new DBManager();
$permission_level = -1;
try {
    if (check_whitelist($uri)) {
        $permission_level = 0;
        pass_to_uri();
    } else if ($sec_man->isset_token() && $sec_man->isset_user_meta()) {
        if ($db->connect()) {

            $token = $sec_man->get_token();
            $user_id = intval($sec_man->get_user_id_by_token($token));
            if ($db->is_valid_token($token, $user_id)) {
                if (is_stats($uri))
                    pass_to_uri();
                else {
                    $permission_level = $sec_man->get_permission_level_for_request($uri, $token, $_SERVER['HTTP_REFERER']);
                    if ($permission_level < 0)
                        throw new PageNotFoundException();
                    if (substr($uri, -4) === '.php') {
                        pass_to_uri();
                    } else if (substr($uri, -1) === '/') {
                        if ($sec_man->can_read_resource($permission_level))
                            include $_SERVER['DOCUMENT_ROOT'] . $uri . 'index.html';
                        else throw new AccessDeniedException(false, true);
                    } else
                        throw new PageNotFoundException();
                }
            } else {
                throw new UnauthorizedException();
            }

        } else {
            $error_code = CodesAndMessages::DB_NOT_AVAILABLE;
        }
    } else {
        throw new UnauthorizedException();
    }
} catch (AccessDeniedException $e) {
    header('HTTP/1.1 403 Forbidden');
    if($e->cantRead)
        header('Location: ' . $ERROR_PAGE . '?error=403');
} catch (PageNotFoundException $e) {
    header('HTTP/1.1 404 Not Found');
    header('Location: ' . $ERROR_PAGE . '?error=404');
} catch (UnauthorizedException $e) {
    $sec_man->clear_token_on_client();
    header('HTTP/1.1 401 Unauthorized');
    header('Location: ' . $ERROR_PAGE . '?error=401');
}
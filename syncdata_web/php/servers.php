<?php
/** @noinspection PhpUndefinedVariableInspection */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
$CUR_DIR = $_SERVER['DOCUMENT_ROOT'] . '/syncdata/php/';
$servers = [];
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_mng = new SecurityManager();
$can_write = $sec_mng->can_write_resource($permission_level);
$can_read = $sec_mng->can_read_resource($permission_level);

function prepareResponseJSON($info, $current_dir)
{
    require_once($current_dir . 'servers_main_info.php');
    if (!empty($info)) {
        $response = array();
        foreach ($info as $server) {
            $avg_con = $server["avg_time_connection"];
            $avg_upld = $server["avg_time_upload"];
            $number_err = $server["number_error"];

            $responseServer = array(
                "host" => $server["host"] . $server["database"] ,
                "last_con" => $server["last_connection"],
                "avg_time_con" => array("value" => $avg_con, "status" => get_status_avg_con($avg_con)),
                "avg_time_upld" => array("value" => $avg_upld, "status" => get_status_avg_upld($avg_upld)),
                "num_err" => array("value" => $number_err, "status" => get_status_number_error($number_err)),
            );
            $response[] = $responseServer;
        }
        return $response;
    }
    return NAN;
}

function get_info_from_server($server, $duration, $list_of_servers, $is_html)
{
    if (isset($server['host']) && isset($server['user']) && isset($server['password']) && isset($server['database']))
        try {
            if ($duration == null)
                $duration = "day";
            $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);
            if (!$mysqli->connect_errno) {

                $mysqli->set_charset("utf8");

                $info = array();

                foreach ($list_of_servers as $one_server) {
                    if($is_html) $info_about_one_server = array("host" => $one_server["host"], 'database' => $one_server["database"]);
                    else $info_about_one_server = array("host" => $one_server["host"] . $one_server["database"]);
                    $sql = "SELECT ID_DATETIME from statistics_syncdata where host_name = '{$one_server["host"]}' and database_name = '{$one_server["database"]}'
                                     and TIME_CONNECTION_MS > 0 ORDER BY id_datetime DESC LIMIT 1";
                    $result = $mysqli->query($sql);
                    $row = mysqli_fetch_array($result);
                    $info_about_one_server["last_connection"] = $row ? $row["ID_DATETIME"] : "NAN";
                    $result->close();
                    $sql = "SELECT avg(TIME_UPLOAD_MS) from statistics_syncdata where HOST_NAME='{$one_server["host"]}' and database_name = '{$one_server["database"]}'
                                             and ID_DATETIME between now() - interval 1 {$duration} and now()
                                             and TIME_UPLOAD_MS > 0";
                    $result = $mysqli->query($sql);
                    $row = mysqli_fetch_array($result);
                    $info_about_one_server["avg_time_upload"] = $row ? round($row["avg(TIME_UPLOAD_MS)"], 3) : 0;
                    $result->close();

                    $sql = "SELECT avg(TIME_CONNECTION_MS) from statistics_syncdata where HOST_NAME='{$one_server["host"]}' and database_name = '{$one_server["database"]}'
                                                 and ID_DATETIME between now() - interval 1 {$duration} and now()
                                                 and TIME_CONNECTION_MS > 0";
                    $result = $mysqli->query($sql);
                    $row = mysqli_fetch_array($result);
                    $info_about_one_server["avg_time_connection"] = $row ? round($row["avg(TIME_CONNECTION_MS)"], 3) : 0;
                    $result->close();

                    $sql = "SELECT count(*) from statistics_syncdata where HOST_NAME='{$one_server["host"]}' and database_name = '{$one_server["database"]}'
                                  and IS_ERROR = 1 
                                  and ID_DATETIME between now() - interval 1 {$duration} and now()";
                    $result = $mysqli->query($sql);
                    $row = mysqli_fetch_array($result);
                    $info_about_one_server["number_error"] = $row ? $row["count(*)"] : -1;
                    $result->close();

                    $info[] = $info_about_one_server;

                }
                $mysqli->close();
                return $info;
            }

        } catch (Exception $e) {
        }
    return false;
}

if ($can_read) {
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        $accept = $_SERVER['HTTP_ACCEPT'];
        $servers = read_config();
        $post = json_decode(file_get_contents('php://input'), true);
        if (isset($post["duration"]) && ($post["duration"] == "day" || $post["duration"] == "week" || $post["duration"] == "hour")) {
            $duration = $post["duration"];
            foreach ($servers as $server) {
                $is_html = $accept == "text/html";
                $answer = get_info_from_server($server, $duration, $servers, $is_html);
                if ($answer) {
                    if ($is_html) {
                        $_servers_main_info = $answer;
                        include $CUR_DIR . 'servers_main_info.php';
                    } else
                        echo json_encode(prepareResponseJSON($answer, $CUR_DIR));

                    break;
                }
            }
        } else echo json_encode(array("message" => "wrong request", "request-body" => $post));
    } else echo json_encode(array("message" => "no headers accept"));
} else throw new AccessDeniedException(false, true);




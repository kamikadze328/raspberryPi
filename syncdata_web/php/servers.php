<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$servers_file = "../../config/sync_data.conf.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}

function prepareResponseJSON($info)
{
    require_once('servers_main_info.php');
    if (!empty($info)) {
        $response = array();
        foreach ($info as $server) {
            $avg_con = $server["avg_time_connection"];
            $avg_upld = $server["avg_time_upload"];
            $number_err = $server["number_error"];

            $responseServer = array(
                "host" => $server["host"],
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

function get_info_from_server($server, $duration){
    try{
        if($duration == null)
            $duration = "day";
        $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

        if (!$mysqli->connect_errno){
            $mysqli->set_charset("utf8");
            global $servers;
            $info = array();
            foreach($servers as $server){
                $info_about_one_server = array("host" => $server["host"]);

                $sql = "SELECT ID_DATETIME from statistics_syncdata where host_name = '{$server["host"]}' 
                                     and TIME_CONNECTION_MS > 0 ORDER BY id_datetime DESC LIMIT 1";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["last_connection"] = $row ? $row["ID_DATETIME"]: "NAN";
                $result->close();

                $sql = "SELECT avg(TIME_UPLOAD_MS) from statistics_syncdata where HOST_NAME='{$server["host"]}' 
                                             and ID_DATETIME between now() - interval 1 {$duration} and now()
                                             and TIME_UPLOAD_MS > 0";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["avg_time_upload"] = $row ? round($row["avg(TIME_UPLOAD_MS)"],3): 0;
                $result->close();

                $sql = "SELECT avg(TIME_CONNECTION_MS) from statistics_syncdata where HOST_NAME='{$server["host"]}' 
                                                 and ID_DATETIME between now() - interval 1 {$duration} and now()
                                                 and TIME_CONNECTION_MS > 0";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["avg_time_connection"] = $row ? round($row["avg(TIME_CONNECTION_MS)"],3): 0;
                $result->close();

                $sql = "SELECT count(*) from statistics_syncdata where HOST_NAME='{$server["host"]}' 
                                  and IS_ERROR = 1 
                                  and ID_DATETIME between now() - interval 1 {$duration} and now()";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["number_error"] = $row ? $row["count(*)"]: -1;
                $result->close();

                $info[] = $info_about_one_server;

            }
            $mysqli->close();
            return $info;
        }

    }catch(Exception $e){}
    return false;
}

if(isset($_SERVER['HTTP_ACCEPT'])) {
    $accept = $_SERVER['HTTP_ACCEPT'];
    $servers = read_json($servers_file);
    $post = json_decode(file_get_contents('php://input'), true);
    if (isset($post["duration"]) && ($post["duration"] == "day" || $post["duration"] == "week" || $post["duration"] == "hour")) {
        $duration = $post["duration"];

        foreach ($servers as $server) {
            $answer = get_info_from_server($server, $duration);
            if ($answer) {
                if ($accept == "text/html") {
                    $_servers_main_info = $answer;
                    include "servers_main_info.php";
                } else
                    echo json_encode(prepareResponseJSON($answer));

                break;
            }
        }
    } else echo json_encode(array("message" => "wrong request", "request-body" => $post));
} else echo json_encode(array("message" => "no headers accept"));



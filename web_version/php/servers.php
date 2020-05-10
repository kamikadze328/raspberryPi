<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$servers_file = "../sync_data.conf.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}

function get_info_from_server($server){
    try{
        $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

        if (!$mysqli->connect_errno){
            $mysqli->set_charset("utf8");
            global $servers;
            $info = array();
            foreach($servers as $server){
                $info_about_one_server = array("host" => $server["host"]);

                $sql = "SELECT ID_DATETIME from statistics where host_name = '{$server["host"]}' 
                                     and TIME_CONNECTION_MS > 0 ORDER BY id_datetime DESC LIMIT 1";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["last_connection"] = $row ? $row["ID_DATETIME"]: NAN;
                $result->close();

                $sql = "SELECT avg(TIME_UPLOAD_MS) from statistics where HOST_NAME='{$server["host"]}' 
                                             and ID_DATETIME between now() - interval 1 hour and now()
                                             and TIME_UPLOAD_MS > 0";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["avg_time_upload"] = $row ? round($row["avg(TIME_UPLOAD_MS)"],3): 0;
                $result->close();

                $sql = "SELECT avg(TIME_CONNECTION_MS) from statistics where HOST_NAME='{$server["host"]}' 
                                                 and ID_DATETIME between now() - interval 1 hour and now()
                                                 and TIME_CONNECTION_MS > 0";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["avg_time_connection"] = $row ? round($row["avg(TIME_CONNECTION_MS)"],3): 0;
                $result->close();

                $sql = "SELECT count(*) from statistics where HOST_NAME='{$server["host"]}' 
                                  and IS_ERROR = 1 
                                  and ID_DATETIME between now() - interval 1 hour and now()";
                $result = $mysqli->query($sql);
                $row = mysqli_fetch_array($result);
                $info_about_one_server["number_error"] = $row ? $row["count(*)"]: -1;
                $result->close();

                array_push($info, $info_about_one_server);

            }
            $mysqli->close();
            return $info;
        }

    }catch(Exception $e){}
    return false;
}

$servers = read_json($servers_file);

$out = array();
foreach($servers as $server){
    $answer = get_info_from_server($server);
    if($answer) {
        $out["info"] = $answer;

        $_servers_main_info = $answer;

        include "servers_main_info.php";
        break;
    }
}

//echo json_encode($out);

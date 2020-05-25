<?php
$servers_file = "../sync_data.conf.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}
function millis_to_date($millis){

    return date("Y-m-d H:i:s", $millis / 1000);
}

function get_now_php($duration){
    return (new DateTime())->modify("-1 {$duration}")->format("Y-m-d H:i:s");
}
function get_graph_from_server($server, $duration, $min_date)
{
        $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

        if (!$mysqli->connect_errno) {
            $mysqli->set_charset("utf8");
            global $servers;
            $info = array();
            foreach ($servers as $server) {
                if($duration == 'day') $format_date = '%Y-%m-%d %H:%i';
                elseif($duration == 'week') $format_date = '%Y-%m-%d %H';
                else $format_date = '%Y-%m-%d %H:%i:%S';
                $sql = "select date_format(ID_DATETIME, '{$format_date}') as date, ROUND(avg(TIME_UPLOAD_MS), 3) as value from statistics 
                            where HOST_NAME='{$server["host"]}' 
                            and ID_DATETIME between '{$min_date}' and now() 
                            and (TIME_CONNECTION_MS < 0 or TIME_CONNECTION_MS is null)
                            group by date";
                $result = $mysqli->query($sql);
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $info_about_one_server = array(
                    "host" => $server["host"],
                    "data" => $rows);
                $result->close();
                $info[] = $info_about_one_server;
            }
            $mysqli->close();
            return $info;
        }

    return false;
}


$post = json_decode(file_get_contents('php://input'), true);
if(isset($post["duration"])  && ($post["duration"] == "day" || $post["duration"] == "week" || $post["duration"] == "hour")) {
    $servers = read_json($servers_file);
        foreach ($servers as $server) {
            $answer = get_graph_from_server($server, $post["duration"], (isset($post["minDate"])) ? millis_to_date($post["minDate"]) : get_now_php($post["duration"]));
            if ($answer) {
                echo json_encode($answer);
                break;
            }
        }
    } else {
    $out["error"] = array("message" => "empty request", "request-body" => $post);
    echo json_encode($out);
}


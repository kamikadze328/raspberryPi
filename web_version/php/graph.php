<?php
$servers_file = "../sync_data.conf.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}
function get_graph_from_server($server, $duration)
{
    try {
        $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

        if (!$mysqli->connect_errno) {
            $mysqli->set_charset("utf8");
            global $servers;
            $info = array();
            foreach ($servers as $server) {
                /* TODO <br />
                <b>Warning</b>:  Illegal string offset 'duration' in <b>C:\OSPanel\domains\openserver\php\graph.php</b> on line <b>41</b><br />
                <br />
                <b>Warning</b>:  Illegal string offset 'duration' in <b>C:\OSPanel\domains\openserver\php\graph.php</b> on line <b>41</b><br />
                <br />
                <b>Warning</b>:  Illegal string offset 'duration' in <b>C:\OSPanel\domains\openserver\php\graph.php</b> on line <b>41</b><br />
                {"error":"empty or wrong request"*/
                if ($duration == "day") $format_date = '%Y-%m-%d %H:%i';
                elseif ($duration == "week") $format_date = '%Y-%m-%d %H';
                else $format_date = "%Y-%m-%d %H:%i:%S";
                $sql = "select date_format(ID_DATETIME, '{$format_date}') as date, ROUND(avg(TIME_UPLOAD_MS), 3) as value from statistics 
                            where HOST_NAME='{$server["host"]}' 
                              and ID_DATETIME between now() - interval 1 {$duration} and now() 
                            group by date";
                $result = $mysqli->query($sql);
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $info_about_one_server = array(
                    "host" => $server["host"],
                    "data" => $rows);
                $result->close();
                $info[] = $info_about_one_server;
            }
            return $info;
        }
    } catch (Exception $e) {
    }
    return false;
}


$post = json_decode(file_get_contents('php://input'), true);
if(!empty($post) && ($post["duration"] == "day" || $post["duration"] == "week" || $post["duration"] == "hour")) {
    $servers = read_json($servers_file);
    foreach ($servers as $server) {
        $answer = get_graph_from_server($server, $post["duration"]);
        if ($answer) {
            echo json_encode($answer);
            break;
        }
    }
}
else
    echo json_encode(array("error" => "empty or wrong request"));


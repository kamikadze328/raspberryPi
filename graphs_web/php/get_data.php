<?php
$servers_file = "../../config/sync_data.conf.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}
function millis_to_date($millis){

    return date("Y-m-d H:i:s", $millis / 1000);
}


function get_tags_str($tags){
    $str = '';
    foreach ($tags as $tag) $str .= "id={$tag} or ";
    return substr($str, 0, -4);
}

function get_graph_from_server($server, $tags, $min_date, $maxDate)
{
    $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

    if (!$mysqli->connect_errno) {
        $mysqli->set_charset("utf8");

        $sql = "select id, ID_DATETIME as date, IF(ID_VALUE > 10000000, null, ID_VALUE) as value
                    from data
                    where ID_DATETIME between '{$min_date}' and '{$maxDate}'
                        and ({$tags})
                    group by id, ID_DATETIME
                    order by id, ID_DATETIME";

        $result = $mysqli->query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $answer = array();
        global $tags_arr;
        $prev_tag = $tags_arr[0];
        $data = array();
        foreach ($rows as $row) {
            if($prev_tag != $row['id']) {
                $answer[] = array('id' => $prev_tag, 'data' => $data);
                $data = array();
                $prev_tag = $row['id'];
            }
            $data[] = array('value' => strcasecmp($row['value'], 'null') ? intval($row['value']) : NAN,
                            'date' => strtotime($row['date'])*1000);

        }
        if(count($rows)) $answer[] = array('id' => $prev_tag, 'data' => $data);

        $result->close();
        $mysqli->close();
        return $answer;
    }

    return false;
}


$post = json_decode(file_get_contents('php://input'), true);
if(isset($post["minDate"]) && isset($post["maxDate"]) && isset($post["tags"])) {
    $servers = read_json($servers_file);
    $tags_arr = $post["tags"];
    foreach ($servers as $server) {
        $answer = get_graph_from_server($server, get_tags_str($post["tags"]), millis_to_date($post["minDate"]), millis_to_date($post["maxDate"]));
        if ($answer) {
            echo json_encode($answer);
            break;
        }
    }
} else {
    $out["error"] = array("message" => "empty request", "request-body" => $post);
    echo json_encode($out);
}

<?php
//$servers_file = "../../config/sync_data.conf.json";
$servers_file = "../sync_data.conf.json";
function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}

function millis_to_date($millis)
{

    return date("Y-m-d H:i:s", $millis / 1000);
}


function get_tags_str($tags)
{
    $str = '';
    foreach ($tags as $tag) $str .= "(data.id={$tag} and tags.id={$tag}) or ";
    return substr($str, 0, -4);
}



function get_graph_from_server($server, $tags, $min_date, $maxDate)
{
    $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

    if (!$mysqli->connect_errno) {
        $mysqli->set_charset("utf8");

        $sql = "select data.ID as id, data.ID_DATETIME as date, IF(data.ID_VALUE > 10000000, null, data.ID_VALUE) as value, tags.TAG_TYPE as type
                    from data, tags
                    where data.ID_DATETIME between '{$min_date}' and '{$maxDate}'
                        and ({$tags})
                    order by id, ID_DATETIME";

        $result = $mysqli->query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $answer = array();
        global $tags_arr;
        $prev_tag = intval($tags_arr[0]);
        if(count($rows) > 0) {
            $prev_type = $rows[0]['type'];
            $data = array();
            foreach ($rows as $row) {
                if ($prev_tag != $row['id']) {
                    $answer[] = array('id' => $prev_tag, 'type' => $row['ID'], 'data' => $data);
                    $data = array();
                    $prev_tag = intval($row['id']);
                    $prev_type = $row['type'];
                }
                $data[] = array(
                    'value' => $row['value'],
                    'date' => strtotime($row['date']) * 1000,
                );

            }
            if (count($rows)) $answer[] = array('id' => $prev_tag, 'type' => $prev_type, 'data' => $data);

            $result->close();
            $mysqli->close();
            return $answer;
        } else return false;
    }

    return false;
}

$post = json_decode(file_get_contents('php://input'), true);
$isOK = false;
$answers = array(
    1 => "no available db servers",
    2 => "no available data",
    3 => "wrong request"
    );
$answer = null;
$errno = null;
$error_message = "internal server error";
if (isset($post["minDate"]) && isset($post["maxDate"]) && isset($post["tags"])) {
    $servers = read_json($servers_file);
    $tags_arr = $post["tags"];
    if (is_array($servers) || is_object($servers)) {
        foreach ($servers as $server) {
            $answer = get_graph_from_server($server, get_tags_str($post["tags"]), millis_to_date($post["minDate"]), millis_to_date($post["maxDate"]));
            if ($answer) {
                echo json_encode($answer);
                $isOK = true;
                break;
            }
        }
        if (!empty($answer)) $errno = 1;
        else $errno = 2;
    } else  $errno = 1;
} else {
    $errno = 3;
}
if (!$isOK) {
    $out["error"] = array("message" => $answers[$errno], "answerDB" => $answer, "request-body" => $post, "errno" => $errno);
    echo json_encode($out);
}



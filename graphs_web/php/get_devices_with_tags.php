<?php
$servers_file = "../../config/sync_data.conf.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}

function get_devices_with_tags($server)
{
    $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

    if (!$mysqli->connect_errno) {
        $mysqli->set_charset("utf8");


        $sql = "select BASE_ADDRESS as id, ID_NAME_FULL as description from devices order by id;";
        $result = $mysqli->query($sql);
        $devices = $result->fetch_all(MYSQLI_ASSOC);
        $result->close();

        $sql = "select id, ID_NAME as description from tags order by id;";
        $result = $mysqli->query($sql);
        $tags = $result->fetch_all(MYSQLI_ASSOC);
        $result->close();

        $mysqli->close();

        $answer = array();
        $counter = 0;
        $current_tags = array();
        foreach($tags as $tag){
                if (intval($devices[$counter]['id'])/1000 != intdiv(intval($tag['id']), 1000)) {
                    $answer[] = array(
                        'id' => intval($devices[$counter]['id']),
                        'description' => $devices[$counter]['description'],
                        'tags' => $current_tags
                    );
                    $current_tags = array();
                    $counter++;

                }
                $current_tags[] = array(
                    'id' => intval($tag['id']),
                    'description' => $tag['description'],
                );
        }
        $answer[] = array(
            'id' => intval($devices[$counter]['id']),
            'description' => $devices[$counter]['description'],
            'tags' => $current_tags
        );
        return $answer;
    }
    return NAN;
}

$answer = null;
$error_message = "internal server error";
$servers = read_json($servers_file);
$tags_arr = $post["tags"];
if (is_array($servers) || is_object($servers)) {
    foreach ($servers as $server) {
        $answer = get_devices_with_tags($server);
        if ($answer) {
            echo json_encode($answer);
            $isOK = true;
            break;
        }
    }
    if (!empty($answer)) $message = "no available db servers";
    else $message = "no available data";
} else $message = "no available db servers";

if (!$isOK) {
    $out["error"] = array("message" => $message, "answerDB" => $answer, "request-body" => $post);
    echo json_encode($out);
}


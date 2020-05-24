<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servers_file = "../sync_data.conf.json";
$tags_file = "../../DATA_UNP300/.Conf_Teg.json";
$devices_file = "../../DATA_UNP300/.Conf_Dev.json";

//$tags_file = "/var/www/html/DATA_UNP300/.Conf_Teg.json";
//$devices_file = "/var/www/html/DATA_UNP300/.Conf_Dev.json";

function read_json($filepath)
{
    if (file_exists($filepath)) return json_decode(file_get_contents($filepath), true);
    else return array();
}
function replace_data($server)
{
    $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

    if (!$mysqli->connect_errno) {
        $mysqli->set_charset("utf8");
        global $devices;
        global $tags;
        $sql = "TRUNCATE TABLE tags; TRUNCATE TABLE devices;";
        $sql .= "INSERT INTO tags()";
        foreach ($tags as $tag) {
            $sql .= "()";
        }
        $sql .= "INSERT INTO device()";
        foreach ($devices as $device) {
            $sql .= "()";
        }

        //$mysqli->multi_query($sql);
        $mysqli->close();
    }
}


$servers = read_json($servers_file);
$tags = json_decode(file_get_contents($tags_file), true);
$devices = json_decode(file_get_contents($devices_file), true);
$answer = array();

foreach ($servers as $server) {
    try{
        replace_data($server);
        $answer[] = array("host" => $server["host"], "status" => true);
    }catch (Exception $e){
        $answer[] = array("host" => $server["host"], "status" => false);
    }
}
echo json_encode($answer);

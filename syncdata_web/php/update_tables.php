<?php
mysqli_report(MYSQLI_REPORT_STRICT);

$servers_file = "../../config/sync_data.conf.json";
//$tags_file = "../../DATA_UNP300/.Conf_Teg.json";
$tags_file = "/var/RAMdisk/DATA_UNP300/.Conf_Teg.json";
//$devices_file = "../../DATA_UNP300/.Conf_Dev.json";
$devices_file = "/var/RAMdisk/DATA_UNP300/.Conf_Dev.json";

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

        $sql .= "INSERT INTO tags(ID, ID_NAME, ID_DEVICE, SAVE_INTERVAL, VALUE_MIN, VALUE_MAX, TAG_TYPE) VALUES ";
        foreach ($tags as $tag) {
            $sql .= "({$tag["iTegAddr"]},'{$tag["sTegInfo"]}',{$tag["iBaseAddrTeg"]},
            {$tag["iTime_Save"]},{$tag["iMinValue"]},{$tag["iMaxValue"]}, '{$tag["sTegType"]}'),";
        }
        $sql = substr($sql, 0, -1);
        $sql .= ";";

        $sql .= "INSERT INTO devices(BASE_ADDRESS, id_name_device, id_name_full, interface_type, INTERFACE_IP_ADDRESS, interface_ip_port, addr_modbus) VALUES ";
        foreach ($devices as $device) {
            $sql .= "({$device["iBaseAddrTeg"]},'{$device["sModelName"]}','{$device["sModuleInfo"]}',
            '{$device["sInterface"]}','{$device["ip_adress"]}',{$device["ip_port"]}, {$device["iAdrMODBUS"]}),";
        }

        $sql = substr($sql, 0, -1);
        $sql .= ";";
        if ($mysqli->multi_query($sql)) {
            while ($mysqli->more_results() && $mysqli->next_result()) {
            }
            if ($mysqli->errno) throw new Exception;
            else {
                $mysqli->commit();
                $mysqli->close();
            }
        } else throw new Exception;
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

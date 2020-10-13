<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_mng = new SecurityManager();
$can_write = $sec_mng->can_write_resource($permission_level);
$can_read = $sec_mng->can_read_resource($permission_level);

mysqli_report(MYSQLI_REPORT_STRICT);
include_once $_SERVER['DOCUMENT_ROOT'].'/api/config/core.php';
$CUR_DIR = $_SERVER['DOCUMENT_ROOT'].'/syncdata/php/';

$tags_file = $_SERVER['DOCUMENT_ROOT'].'/unp/RAMdisk/DATA_UNP300/.Conf_Teg.json';
$devices_file = $_SERVER['DOCUMENT_ROOT'].'/unp/RAMdisk/DATA_UNP300/.Conf_Dev.json';


function replace_data($server, $devices, $tags)
{
    if (isset($server['host']) && isset($server['user']) && isset($server['password']) && isset($server['database']))
        try {
            $mysqli = new mysqli($server["host"], $server["user"], $server["password"], $server["database"]);

            if (!$mysqli->connect_errno) {
                $mysqli->set_charset("utf8");
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
                return true;
            }
        } catch (Exception $e) {
        }
    return false;

}

if ($can_write) {
    $servers = read_config();
    $tags = json_decode(file_get_contents($tags_file), true);
    $devices = json_decode(file_get_contents($devices_file), true);
    $answer = array();

    foreach ($servers as $server) {
        $answer[] = array("host" => $server["host"], "status" => !!replace_data($server, $devices, $tags));
    }
    echo json_encode($answer);
} else throw new AccessDeniedException(true, false);


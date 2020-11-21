<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_mng = new SecurityManager();
$can_write = $sec_mng->can_write_resource($permission_level);
$can_read = $sec_mng->can_read_resource($permission_level);

include_once $_SERVER['DOCUMENT_ROOT'] . '/unp/php/LIST_PATHS.php';

function get_file($filepath)
{
    global $TEMP_FLAG;
    for ($i = 0; $i < 3; $i++)
        if (file_exists($TEMP_FLAG))
            sleep(1);
        else
            if (file_exists($filepath))
                return file_get_contents($filepath);
    return "error ==> file no exists";
}

if ($can_read) {

    if (isset($_POST['sContentName'])) {
        $sContentName = $_POST['sContentName'];

        if ($sContentName == 'devices')
            $filepath = $RAMdisk_DIR . '.Conf_Dev.json';

        elseif ($sContentName == 'tags')
            $filepath = $RAMdisk_DIR . '.Conf_Teg.json';

        elseif ($sContentName == 'dynData')
            $filepath = $RAMdisk_DIR . '.DynDATA.json';

        elseif ($sContentName == 'alarms')
            $filepath = $RAMdisk_DIR . '.Alarm.json';

        elseif ($sContentName == 'logAutomation')
            $filepath = $RAMdisk_DIR . '.LogAutomation.txt';

        elseif ($sContentName == 'clientsIP')
            $filepath = $RAMdisk_DIR . '.ClientsIP.json';

        elseif ($sContentName == 'settings')
            $filepath = $UNP_DIR . '..settingUNP';

        if (isset($filepath))
            echo get_file($filepath);
    }
} else throw new AccessDeniedException(false, true);


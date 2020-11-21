<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
$sec_mng = new SecurityManager();
$can_write = $sec_mng->can_write_resource($permission_level);
$can_read = $sec_mng->can_read_resource($permission_level);

include_once $_SERVER['DOCUMENT_ROOT'] . '/api/unp/LIST_PATHS.php';
$data_dir = FACTORY_DATA($DATA_DIR, $_GET['factory']);
if ($can_write) {

    if (isset($_POST['sContentName'])) {

        $filedata = $_POST['sFileData'];      // считываем содержимое файла

        if ($_POST['sContentName'] == 'settings')
            $filename = $UNP_DIR . '..settingUNP';      // считываем имя файла

        elseif ($_POST['sContentName'] == 'command')
            $filename = $data_dir . round(microtime(true) * 1000) . '.comand';

        elseif ($_POST['sContentName'] == 'dynDATA')
            $filename = $data_dir . '.DynDATA.json';

        elseif (isset($_POST['sFileName']))
            $filename = $_POST['sFileName'];


        $fd = fopen($filename, 'w');
        chmod($filename, 0777);
        fwrite($fd, $filedata);
        fclose($fd);
        //echo $filename;   chmod($filename, 0777);

    }
} else throw new AccessDeniedException(true, false);


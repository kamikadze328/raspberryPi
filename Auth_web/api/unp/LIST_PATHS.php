<?php
$DATA_DIR = $_SERVER['DOCUMENT_ROOT'].'/unp/DATA/';
$UNP_DIR = '/home/pi/unp/';
$IS_LOCALHOST = $_SERVER['DOCUMENT_ROOT'].'/unp/.isNotLocalhost';
$TEMP_FLAG = $DATA_DIR.'.tempFTPUploadFlag';

function FACTORY_DATA($dir_path, $id){
    return $dir_path .'FACTORY_'.$id.'/';
}
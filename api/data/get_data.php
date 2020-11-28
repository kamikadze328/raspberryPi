<?php
/** @noinspection PhpUndefinedVariableInspection */

if (isset($user_data['minDate']) && isset($user_data["maxDate"]) && isset($user_data["tags"])) {
    $min_date = floor(intval($user_data['minDate']) / 1000);
    $max_date = floor(intval($user_data['maxDate']) / 1000);
    $data = $db->get_data($user_data["tags"], $min_date, $max_date);
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
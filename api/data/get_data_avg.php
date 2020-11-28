<?php
/** @noinspection PhpUndefinedVariableInspection */

if (isset($user_data['minDate']) && isset($user_data["maxDate"]) && isset($user_data['configs'])) {
    $min_date = floor(intval($user_data['minDate']) / 1000);
    $max_date = floor(intval($user_data['maxDate']) / 1000);
    foreach ($user_data['configs'] as $conf) {
        if (isset($conf['id']) && isset($conf['tags'])) {
            $data[] = array(
                'id' => $conf['id'],
                'data' => $db->get_data_avg($conf['tags'], $min_date, $max_date)
            );
        } else {
            $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
            break;
        }
    }
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}
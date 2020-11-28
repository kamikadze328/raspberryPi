<?php
/** @noinspection PhpUndefinedVariableInspection */
if(isset($user_data["tags"])) {
    $data = $db->get_dyn_data($user_data["tags"]);
} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}